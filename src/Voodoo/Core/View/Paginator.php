<?php

/**
 * -----------------------------------------------------------------------------
 * Paginator
 * -----------------------------------------------------------------------------
 * @author      Mardix
 * @github      http://github.com/mardix/Paginator
 * @package     VoodooPHP
 *
 * @copyright   (c) 2012 Mardix -> http://github.com/mardix :)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Paginator
 * @since       Apr 3, 2012
 * @desc        A simple pagination class.
 * @version     1.2.2 (Apr 7 2012)
 *
 *
 * ABOUT
 * -----
 * Paginator is a simple class that allows you to create pagination for your application.
 * It doesn't require any database connection. It is compatible with Twitter's Bootstrap Framework, by using the CSS class pagination.
 * So it can be implemented quickly in your existing settings.
 *
 *
 * How it works
 * -----------
 * It reads the $queryUrl ( http://xyz.x/page/253 ) that was provided and based on the regexp pattern (ie: /page/(:num))
 * it extract the page number and build the pagination for all the page numbers. If the page number does't exist, i will create for you based on the pattern
 *
 * About $pagePattern (:num)
 * -----------
 * (:num) is our regex pattern to capture the page number and pass it to generate the pagination.
 * It is require to catch the page number properly
 *
 *  /page/(:num) , will capture page in this pattern http://xyz.com/page/252
 *
 *  page=(:num) , will capture the pattern http://xyz.com/?page=252
 *
 *  Any other regexp pattern will work also
 *
 * When a query url is set without the page number, automatically based on the page pattern, the page number will be added
 * i.e:
 *     $queryUrl = http://xyz.com/?q=boom
 *     $pagePattern = page=(:num)
 *
 *     the page number will be added as so at the end of the query
 *     http://xyz.com/?q=boom&page=2
 *
 *
 *
 * Example
 * With friendly url:
 * ------------------
 *      $siteUrl = "http://www.givemebeats.net/buy-beats/Hip-Hop-Rap/page/4/";
 *      $pagePattern = "/page/(:num)";
 *      $totalItems = 225;
 *      $Paginator = new Paginator($siteUrl,$pagePattern);
 *      $Pagination = $Paginator($totalItems);
 *      print($Pagination);
 *
 *
 * With non friendly url:
 * ------------------
 *      $siteUrl = "http://www.givemebeats.net/buy-beats/?genre=Hip-Hop-Rap&page=4";
 *      $pagePattern = "page=(:num)";
 *      $totalItems = 225;
 *      $Paginator = new Paginator($siteUrl,$pagePattern);
 *      $Pagination = $Paginator($totalItems);
 *      print($Pagination);
 *
 *
 * Quick way:
 * ---------
 *      Paginator::CreateWithUri($pagePattern,$totalItems);
 *
 *
 * Major Methods
 * -----------------
 *
 * - __construct()                        : Instantiate the class
 *
 * - setQueryUrl($queryUrl,$pagePattern)  : To set the url that will be used to create the pagination. pagePattern is a regex to catch the page number in the queryUrl
 *
 * - setTotalItems($totalItems)           : Set the total items. It is required so it create the proper page count etc
 *
 * - setItemsPerPage($ipp)                : Total items to display in your results page. This count will allow it to properly count pages
 *
 * - setNavigationSize($nav)              : Crete the size of the pagination like [1][2][3][4][next]
 *
 * - setPrevNextTitle(Prev,Next)          : To set the action next and previous
 *
 * - toArray($totalItems)                 : Return the pagination in array. Use it if you want to use your own template to generate the pagination in HTML
 *
 * - render($totalItems)                  : Return the pagination in HTML format
 *
 *
 *
 * Other methods to access and update data before rendering
 *
 * - getCurrentPage()                   : Return the current page number
 *
 * - getTotalPages()                    : Return the total pages
 *
 * - getStartCount()                    : The start count.
 *
 * - getEndCount()                      : The end count
 *
 * - getSQLOffest()                     : When using SQL query, you can use this method to give you the limit count like: 119,10 which will be used in "LIMIT 119,10"
 *
 * - getItemsPerPage()                  : Return the total items per page
 *
 *
 */

namespace Voodoo\Core\View;

class Paginator
{
    const NAME = "Paginator";
    const VERSION = "1.2.2.1";

    /**
     * Holds params
     * @var array
     */
    protected $params = array();

    /**
     * Holds the template url
     * @var string
     */
    protected $templateUrl = "";

    /**
     * Create the Paginator with the REQUEST_URI. It's a shortcut to quickly build it with the request URI
     * @param  regex     $pagePattern    - a regex pattern that will match the url and extract the page number
     * @param  int       $totalItems     - Total items found
     * @param  int       $itemPerPage    - Total items per page
     * @param  int       $navigationSize - The naviagation set size
     * @return Paginator
     */
    public static function CreateWithUri($pagePattern = "/page/(:num)", $totalItems = 0, $itemPerPage = 10, $navigationSize = 10)
    {
        return new self($_SERVER["REQUEST_URI"], $pagePattern, $totalItems, $itemPerPage, $navigationSize);
    }

    /**
     * Constructor
     * @param string $queryUrl       - The url of the pagination
     * @param regex  $pagePattern    - a regex pattern that will match the url and extract the page number
     * @param int    $totalItems     - Total items found
     * @param int    $itemPerPage    - Total items per page
     * @param int    $navigationSize - The naviagation set size
     */
    public function __construct($queryUrl = "", $pagePattern = "/page/(:num)", $totalItems = 0, $itemPerPage = 10, $navigationSize = 10)
    {
        if ($queryUrl)
            $this->setQueryUrl($queryUrl, $pagePattern);

        $this->setTotalItems($totalItems);

        $this->setItemsPerPage($itemPerPage);

        $this->setNavigationSize($navigationSize);

        $this->setPrevNextTitle();
    }

    /**
     * Set the URL, automatically it will parse every thing to it
     * @param  type      $url
     * @return Paginator
     */
    public function setQueryUrl($url, $pagePattern = "/page/(:num)")
    {
        $pattern = str_replace("(:num)", "([0-9]+)", $pagePattern);

        preg_match("~$pattern~i", $url, $m);

        /**
         * No match found.
         * We'll add the pagination in the url, so this way it can be ready for next pages.
         * This way a url http://xyz.com/?q=boom , becomes http://xyz.com/?q=boom&page=2
         */
        if (count($m) == 0) {

            $pag_ = str_replace("(:num)", 0, $pagePattern);

            /**
             * page pattern contain the equal sign, we'll add it to the query ?page=123
             */
            if (strpos($pagePattern, "=") !== false) {

                if (strpos($url, "?") !== false)
                    $url .= "&" . $pag_;
                else
                    $url .= "?" . $pag_;

                return
                        $this->setQueryUrl($url, $pagePattern);
            }

            /**
             * Friendly url : /page/123
             */
            else if (strpos($pagePattern, "/") !== false) {

                if (strpos($url, "?") !== false) {
                    list($segment, $query) = explode("?", $url, 2);

                    if (preg_match("/\/$/", $segment)) {
                        $url = $segment . (preg_replace("/^\//", "", $pag_));
                        $url .= ((preg_match("/\/$/", $pag_)) ? "" : "/") . "?{$query}";
                    } else {
                        $url = $segment . $pag_;
                        $url .= ((preg_match("/\/$/", $pag_)) ? "" : "/") . "?{$query}";
                    }
                } else {
                    if (preg_match("/\/$/", $segment))
                        $url .= (preg_replace("/^\//", "", $pag_));
                    else
                        $url .= $pag_;
                }

                return
                        $this->setQueryUrl($url, $pagePattern);
            }
        }

        $match = current($m);
        $last = end($m);
        $page = $last ? $last : 1;

        /**
         * TemplateUrl will be used to create all the page numbers
         */
        $this->templateUrl = str_replace($match, preg_replace("/[0-9]+/", "(#pageNumber)", $match), $url);

        $this->setCurrentPage($page);

        return
                $this;
    }

    /**
     * To set the previous and next title
     * @param  type      $prev : Prev | &laquo; | &larr;
     * @param  type      $next : Next | &raquo; | &rarr;
     * @return Paginator
     */
    public function setPrevNextTitle($prev = "Prev", $next = "Next")
    {
        $this->params["prevTitle"] = $prev;
        $this->params["nextTitle"] = $next;

        return
                $this;
    }

    /**
     * Set the total items. It will be used to determined the size of the pagination set
     * @param  int       $items
     * @return Paginator
     */
    public function setTotalItems($items = 0)
    {
        $this->params["totalItems"] = $items;

        return
                $this;
    }

    /**
     * Get the total items
     * @return int
     */
    public function getTotalItems()
    {
        return
                $this->params["totalItems"];
    }

    /**
     * Set the items per page
     * @param  type      $ipp
     * @return Paginator
     */
    public function setItemsPerPage($ipp = 10)
    {
        $this->params["itemsPerPage"] = $ipp;

        return
                $this;
    }

    /**
     * Retrieve the items per page
     * @return int
     */
    public function getItemsPerPage()
    {
        return
                $this->params["itemsPerPage"];
    }

    /**
     * Set the current page
     * @param  int       $page
     * @return Paginator
     */
    public function setCurrentPage($page = 1)
    {
        $this->params["currentPage"] = $page;

        return
                $this;
    }

    /**
     * Get the current page
     * @return type
     */
    public function getCurrentPage()
    {
        return
                ($this->params["currentPage"] <= $this->getTotalPages()) ? $this->params["currentPage"] : $this->getTotalPages();
    }

    /**
     * Get the pagination start count
     * @return int
     */
    public function getStartCount()
    {
        return
                (int) ($this->getItemsPerPage() * ($this->getCurrentPage() - 1));
    }

    /**
     * Get the pagination end count
     * @return int
     */
    public function getEndCount()
    {
        return
                (int) ((($this->getItemsPerPage() - 1) * $this->getCurrentPage()) + $this->getCurrentPage() );
    }

    /**
     * Return the offset for sql queries, specially
     * @return START,LIMIT
     *
     * @tip: SQL tip. It's best to do two queries one with SELECT COUNT(*) FROM tableName WHERE X
     *       set the setTotalItems()
     */
    public function getSQLOffset()
    {
        return
                $this->getStartCount() . "," . $this->getItemsPerPage();
    }

    /**
     * Get the total pages
     * @return int
     */
    public function getTotalPages()
    {
        return
                @ceil($this->getTotalItems() / $this->getItemsPerPage());
    }

    /**
     * Set the navigation size
     * @param  int       $set
     * @return Paginator
     */
    public function setNavigationSize($set = 10)
    {
        $this->params["navSize"] = $set;

        return
                $this;
    }

    /**
     * Get the navigation size
     * @return int
     */
    public function getNavigationSize()
    {
        return
                $this->params["navSize"];
    }

    /*     * **************************************************************************** */

    /**
     * toArray() export the pagination into an array. This array can be used for your own template or for other usafe
     * @param  int   $totalItems - the total Items found
     * @return Array
     *     Array(
     *          array(
     *                "PageNumber", // the page number
     *                "Label", // the label for the page number
     *                "Url", // the url
     *                "isCurrent" // bool  set if page is current or not
     *          )
     *      )
     */
    public function toArray($totalItems = 0)
    {
        $Navigation = array();

        if ($totalItems)
            $this->setTotalItems($totalItems);

        $totalPages = $this->getTotalPages();
        $navSize = $this->getNavigationSize();
        $currentPage = $this->getCurrentPage();

        if ($totalPages) {

            $halfSet = @ceil($navSize / 2);
            $start = 1;
            $end = ($totalPages < $navSize) ? $totalPages : $navSize;

            $usePrevNextNav = ($totalPages > $navSize) ? true : false;

            if ($currentPage >= $navSize) {
                $start = $currentPage - $navSize + $halfSet + 1;
                $end = $currentPage + $halfSet - 1;
            }

            if ($end > $totalPages) {
                $s = $totalPages - $navSize;
                $start = $s ? $s : 1;
                $end = $totalPages;
            }

            // Previous
            $prev = $currentPage - 1;
            if ($currentPage >= $navSize && $usePrevNextNav) {
                $Navigation[] = array(
                    "PageNumber" => $prev,
                    "Label" => $this->prevTitle,
                    "Url" => $this->parseTplUrl($prev),
                    "isCurrent" => false
                );
            }

            // All the pages
            for ($i = $start; $i <= $end; $i++) {
                $Navigation[] = array(
                    "PageNumber" => $i,
                    "Label" => $i,
                    "Url" => $this->parseTplUrl($i),
                    "isCurrent" => ($i == $currentPage) ? true : false,
                );
            }

            // Next
            $next = $currentPage + 1;
            if ($next < $totalPages && $end < $totalPages && $usePrevNextNav) {
                $Navigation[] = array(
                    "PageNumber" => $next,
                    "Label" => $this->nextTitle,
                    "Url" => $this->parseTplUrl($next),
                    "isCurrent" => false
                );
            }
        }

        return
                $Navigation;
    }

    /**
     * Render the paginator in HTML format
     * @param  int    $totalItems        - The total Items
     * @param  string $paginationClsName - The class name of the pagination
     * @param  string $wrapTag
     * @param  string $listTag
     * @return string
     * <div class='pagination'>
     *      <ul>
     *          <li>1</li>
     *          <li class='active'>2</li>
     *          <li>3</li>
     *      <ul>
     * </div>
     */
    public function render($totalItems = 0, $paginationClsName = "pagination", $wrapTag = "ul", $listTag = "li")
    {
        $this->listTag = $listTag;

        $this->wrapTag = $wrapTag;

        foreach ($this->toArray($totalItems) as $page) {
            $pagination .= $this->wrapList($this->aHref($page["Url"], $page["Label"]), $page["isCurrent"], false);
        }

        return
                "<div class=\"{$paginationClsName}\">
                <{$this->wrapTag}>{$pagination}</{$this->wrapTag}>
            </div>";
    }

    /*     * **************************************************************************** */

    /**
     * Parse a page number in the template url
     * @param  int    $pageNumber
     * @return string
     */
    protected function parseTplUrl($pageNumber)
    {
        return
                str_replace("(#pageNumber)", $pageNumber, $this->templateUrl);
    }

    /**
     * To create an <a href> link
     * @param  int    $pageNumber
     * @param  string $txt
     * @return string
     */
    protected function aHref($url, $txt)
    {
        return
                "<a href=\"{$url}\">{$txt}</a>";
    }

    /**
     * Create a wrap list, ie: <li></li>
     * @param  string $html
     * @param  bool   $isActive   - To set the active class in this element
     * @param  bool   $isDisabled - To set the disabled class in this element
     * @return string
     */
    protected function wrapList($html, $isActive = false, $isDisabled = false)
    {
        $activeCls = $isActive ? " active " : "";
        $disableCls = $isDisabled ? " disabled " : "";

        return
                "<{$this->listTag} class=\"{$activeCls} {$disableCls}\">{$html}</{$this->listTag}>\n";
    }

    /*     * **************************************************************************** */

    /** MAGIC METHODS TAAADDAAAAA!!!! * */
    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function __get($key)
    {
        return
                $this->params[$key];
    }

    public function __toString()
    {
        return
                $this->render();
    }

}
