<?php

/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2014 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Controller\TPagination
 * @desc        Create the Voodoo\Paginator instance for paginator attached to the controller
 *
 *
 */

namespace Voodoo\Core\Controller;

use Voodoo;

trait TPagination
{
    private $paginator = null;

    /**
     * Access the Paginator object
     * 
     * @return type
     */
    public function pagination()
    {
        if (! $this->paginator) {
            $this->setPagination();
        }
        return $this->paginator;
    }
    
    /**
     * Pagination setter
     *
     * @param int $totalItems - Set the total items
     * @param int $itemsPerPage - Total items per page
     * @param int $itemsPerPage - Total items per page
     * @return Voodoo\Paginator
     */
    public function setPagination($totalItems = null, $itemsPerPage = null,
                                    $navigationSize = null)
    {
        if (! $this->paginator) {
            $pattern = $this->getConfig("views.pagination.pagePattern");
            $this->paginator = new Voodoo\Paginator($pattern);
        }

        if ($totalItems) {
            $itemsPerPage = $itemsPerPage ?: $this->getConfig("views.pagination.itemsPerPage");
            $navigationSize = $this->getConfig("views.pagination.navigationSize");
            $this->paginator->setItems($totalItems, $itemsPerPage, $navigationSize);
        }
        return $this->paginator;
    }



    /**
     * Pagination Setter to paginate from Voodoo\Core\Model object from Voodoo\VoodOrm
     * It will assign the start and limit fof the pagination
     *
     * @param Voodoo\Core\Model $model
     * @param type $itemsPerPage
     * @param type $navigationSize
     * @return Voodoo\Paginator
     */
    public function setPaginationFromModel(Voodoo\Core\Model $model,
                                            $itemsPerPage = null,
                                            $navigationSize = null)
    {
        $cloneModel = clone $model;
        $totalItems = $cloneModel->count();
        $this->setPagination($totalItems, $itemsPerPage, $navigationSize);

        $model->limit($this->paginator->getPerPage())
                ->offset($this->paginator->getStart());
        return $this->paginator;
    }

    /**
     * Return if the pagination is set
     *
     * @return bool
     */
    public function issetPagination()
    {
        return ($this->paginator) ? true : false;
    }
}
