<?php

namespace WPStaging\Pro\Database\Legacy\Command;

use SplObjectStorage;
use WPStaging\Pro\Database\Legacy\Command\Dto\BackupDto;
use WPStaging\Framework\Command\CommandInterface;
use WPStaging\Framework\Command\HandlerInterface;

class BackupHandler implements HandlerInterface
{
    /** @var SplObjectStorage */
    private $commands;

    /** @var BackupDto */
    private $dto;

    public function __construct(BackupDto $dto)
    {
        $this->dto = $dto;
        $this->commands = new SplObjectStorage();
    }

    public function addCommand(CommandInterface $command)
    {
        /** @var AbstractBackupCommand $command */
        if ($this->commands->contains($command)) {
            return;
        }

        $command->setDto($this->dto);

        $this->commands->attach($command);
    }

    /**
     * @param string|null $action
     */
    public function handle($action = null)
    {
        /** @var CommandInterface $command */
        foreach ($this->commands as $command) {
            $command->execute();
        }
    }
}
