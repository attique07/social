<?php


namespace Packages\ShaunSocial\Core\Traits;

trait IsSubject
{
    public function getSubjectType()
    {
        return $this->getTable();
    }

    public static function getResourceClass()
    {
        return '';
    }

    public function getTitle()
    {
        return '';
    }

    public function getHref()
    {
        return '';
    }

    public function getAdminHref()
    {
        return $this->getHref();
    }

    public function getOgImage()
    {
        return '';
    }
}
