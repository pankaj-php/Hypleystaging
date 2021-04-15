<?php

namespace WPStaging\Pro\Backup\Abstracts\Dto\Traits;

trait DateCreatedTrait
{
    /** @var string */
    private $dateCreated;

    /** @var string */
    private $dateCreatedTimezone;

    /**
     * @return string
     */
    public function getIdByDateCreated()
    {
        return $this->getDateCreated();
    }

    /**
     * @return string
     */
    public function getDateCreatedFormatted()
    {
        return date_i18n('Y/m/d H:i', $this->getDateCreated(), $this->getDateCreatedTimezone());
    }

    /**
     * @return string
     */
    public function getDateCreated()
    {
        return (string)$this->dateCreated;
    }

    /**
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return string
     */
    public function getDateCreatedTimezone()
    {
        return (string)$this->dateCreatedTimezone;
    }

    /**
     * @param string $dateCreatedTimezone
     */
    public function setDateCreatedTimezone($dateCreatedTimezone)
    {
        $this->dateCreatedTimezone = $dateCreatedTimezone;
    }
}
