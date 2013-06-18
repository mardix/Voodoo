<?php

/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/mardix/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2013 Mardix (http://github.com/mardix)
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
     * @param int $totalItems - Set the total items 
     * @param int $itemsPerPage - Total items per page
     * @param string $uri - By default it will create a url from the URI, 
     *                      change it to set it to another url without the page # pattern
     * 
     * @return Voodoo\Paginator
     */
    public function pagination($totalItems = null, $itemsPerPage = null, $uri = null)
    {
        if (! $this->paginator) {
            if(! $uri) {
                $uri = $this->getBaseUrl();
                $uri .= $this->getRequestURI();
            }
            $pattern = $this->getConfig("views.pagination.pagePattern");
            $itemsPerPage = $itemsPerPage ?: $this->getConfig("views.pagination.itemsPerPage");
            $navigationSize = $this->getConfig("views.pagination.navigationSize");
            
            $this->paginator = new Voodoo\Paginator($uri, $pattern);
            $this->paginator->setItemsPerPage($itemsPerPage)
                            ->setNavigationSize($navigationSize);
        }
        if (is_numeric($totalItems)) {
            $this->paginator->setTotalItems($totalItems);
        }
        return $this->paginator;
    }    
           
}



