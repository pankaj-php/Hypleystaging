<?php

namespace WPStaging\Pro\Backup\Abstracts\Task;

use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Queue\Queue;
use WPStaging\Framework\Queue\Storage\CacheStorage;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Abstracts\Dto\TaskResponseDto;
use WPStaging\Pro\Backup\Abstracts\Task\RestoreHandlers\RestoreTaskProcessor;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Class RestoreTask
 *
 * This is an abstract class for the filesystem-based restore actions of importing a site,
 * such as plugins, themes, mu-plugins and uploads files.
 *
 * It's main philosophy is to control the individual queue of what needs to be processed
 * from each of the concrete restores. It delegates actual processing of the queue to a separate class.
 *
 * @package WPStaging\Pro\Backup\Abstracts\Task
 */
abstract class RestoreTask extends AbstractTask
{
    // Allows to use isThreshold()
    use ResourceTrait;

    protected $filesystem;

    private $restoreQueue;
    private $restoreQueueStorage;
    private $taskProcessor;

    public function __construct(LoggerInterface $logger, Cache $cache, Filesystem $filesystem, Queue $restoreQueue, CacheStorage $restoreQueueStorage, RestoreTaskProcessor $taskProcessor)
    {
        parent::__construct($logger, $cache);
        $this->filesystem          = $filesystem;
        $this->restoreQueue        = $restoreQueue;
        $this->restoreQueueStorage = $restoreQueueStorage;
        $this->taskProcessor       = $taskProcessor;
    }

    public function prepare()
    {
        parent::prepare();

        if (!$this->queueExists()) {
            // Initialize and populate queue
            $this->restoreQueueStorage->setKey($this->getTaskName() . '_RestoreQueue');
            $this->restoreQueue->setStorage($this->restoreQueueStorage);

            $this->buildQueue();

            $this->logger->info(sprintf(
                __('%s: Enqueued %d actions', 'wp-staging'),
                $this->getStatusTitle(),
                $this->restoreQueue->count()
            ));
        } else {
            // Just initialize queue
            $this->restoreQueueStorage->setKey($this->getTaskName() . '_RestoreQueue');
            $this->restoreQueue->setStorage($this->restoreQueueStorage);
        }
    }

    /**
     * @return TaskResponseDto
     */
    public function execute()
    {
        $this->prepare();

        if ($this->requestDto->getSteps()->getTotal() === 0) {
            $this->requestDto->getSteps()->setTotal($this->restoreQueue->count());
        }

        if ($this->restoreQueue->count() > 0) {
            $this->processNextItemInQueue();
        }

        return $this->generateResponse();
    }

    /**
     * Concrete classes of the RestoreTask must build
     * the queue once, enqueuing everything that needs
     * to be moved or deleted, using $this->enqueueMove
     * or $this->enqueueDelete.
     *
     * @return void
     */
    abstract protected function buildQueue();

    /**
     * @return bool True if queue already exists. False if not.
     */
    private function queueExists()
    {
        return $this->restoreQueueStorage->getCache()->cacheExists('queue_' . $this->getTaskName() . '_RestoreQueue');
    }

    /**
     * Executes the next item in the queue.
     */
    protected function processNextItemInQueue()
    {
        $nextInQueue = $this->restoreQueue->pop();

        // Make sure we read expected data from the queue
        if (!is_array($nextInQueue)) {
            $this->logger->warning(sprintf(
                __('%s: An internal error occurred that prevented this file from being restored. No changes have been made. (Error Code: INVALID_QUEUE_ITEM)', 'wp-staging'),
                $this->getStatusTitle()
            ));
            $this->logger->debug(wp_json_encode($nextInQueue));

            return;
        }

        // Make sure data is in the expected format
        array_map(function ($requiredKey) use ($nextInQueue) {
            if (!array_key_exists($requiredKey, $nextInQueue)) {
                $this->logger->warning(sprintf(
                    __('%s: An internal error occurred that prevented this file from being restored. No changes have been made. (Error Code: INVALID_QUEUE_ITEM)', 'wp-staging'),
                    $this->getStatusTitle()
                ));
                $this->logger->debug(wp_json_encode($nextInQueue));

                return;
            }
        }, ['action', 'source', 'destination']);

        $source = $nextInQueue['source'];

        // Make sure destination is within WordPress
        // @todo Test exporting in Windows and importing in Linux and vice-versa
        $destination = $nextInQueue['destination'];
        $destination = wp_normalize_path($destination);
        $destination = ABSPATH . str_replace(wp_normalize_path(ABSPATH), '', $destination);

        // Executes the action
        $this->taskProcessor->handle($nextInQueue['action'], $source, $destination, $this, $this->logger);
    }

    /**
     * @param string $source          Source path to move.
     * @param string $destination     Where to move source to.
     * @param string $positionInQueue Controls the position of this action in the queue.
     */
    public function enqueueMove($source, $destination, $positionInQueue = 'last')
    {
        $this->enqueue([
            'action'      => 'move',
            'source'      => $source,
            'destination' => $destination,
        ], $positionInQueue);
    }

    /**
     * @param string $source          Source path to move.
     * @param string $destination     Where to move source to.
     * @param string $positionInQueue Controls the position of this action in the queue.
     */
    public function enqueueOverwrite($source, $destination, $positionInQueue = 'last')
    {
        $this->enqueue([
            'action'      => 'copy',
            'source'      => $source,
            'destination' => $destination,
        ], $positionInQueue);
    }

    /**
     * @param string $path            The path to delete. Can be a folder, which will be deleted recursively.
     * @param string $positionInQueue Controls the position of this action in the queue.
     */
    public function enqueueDelete($path, $positionInQueue = 'last')
    {
        $this->enqueue([
            'action'      => 'delete',
            'source'      => '',
            'destination' => $path,
        ], $positionInQueue);
    }

    /**
     * @param array  $action          An array of actions to perform.
     * @param string $positionInQueue Controls the position of this action in the queue.
     */
    private function enqueue($action, $positionInQueue)
    {
        if ($positionInQueue === 'last') {
            /*
             * Position "last" is the default.
             * Technically speaking, it's FIFO. First in, first out, like in a supermarket queue.
             */
            $this->restoreQueue->push($action);
        } elseif ($positionInQueue === 'first') {
            /*
             * Position "first", LIFO, Last in, first out.
             * Good for retrying an action, as if your credit card failed in the supermarket
             * and you're trying again with another machine without going back to the end of the queue.
             */
            $this->restoreQueue->prepend($action);
        }
    }
}
