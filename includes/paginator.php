<?php

class paginator
{

    private $totalCount       = 0;
    private $totalPages       = 0;
    private $currentPage      = 0;
    private $recordsPerPage   = 10;
    private $pagesPerBlock    = 9;
    private $viewStartEnd     = true;
    private $is_searching     = false;
    private $urlPattern;

    public function __construct($totalCount, $currentPage, $recordsPerPage=10, $pagesPerBlock=9, $urlPattern = '', $viewStartEnd = FALSE, $is_searching = false)
    {

        $this->totalCount     = $totalCount;
        $this->recordsPerPage = $recordsPerPage;
        $this->currentPage    = $currentPage;
        $this->urlPattern     = $urlPattern;
        $this->pagesPerBlock  = $pagesPerBlock;
        $this->viewStartEnd   = $viewStartEnd;
        $this->is_searching   = $is_searching;

        $this->totalPages     = ($this->recordsPerPage == 0 ? 0 : (int) ceil($this->totalCount/$this->recordsPerPage));
        $this->nextPage       = $this->currentPage < $this->totalPages ? $this->currentPage + 1 : NULL;
        $this->prevPage       = $this->currentPage > 1 ? $this->currentPage - 1 : NULL;

    }

    public static function getHtml($totalCount, $currentPage, $recordsPerPage=10, $pagesPerBlock=9, $urlPattern = '', $viewStartEnd = true, $is_searching = false)
    {

        $paginator = new paginator($totalCount, $currentPage, $recordsPerPage, $pagesPerBlock=9, $urlPattern, $viewStartEnd, $is_searching);
        return $paginator->toHtml();

    }

    public static function get($totalCount, $currentPage, $recordsPerPage=10, $pagesPerBlock=9, $urlPattern = '', $viewStartEnd = FALSE, $is_searching = false)
    {

        $paginator = new paginator($totalCount, $currentPage, $recordsPerPage, $pagesPerBlock=9, $urlPattern, $viewStartEnd, $is_searching);
        return $paginator->toArray();

    }

    private function getPageUrl($pageNum=NULL)
    {

        if(!$pageNum)
        {
            return NULL;
        }
        return str_replace('(:page)', $pageNum, $this->urlPattern);

    }

    private function createPage($pageNum, $isCurrent = FALSE)
    {

        return array(
            'num'       => $pageNum,
            'url'       => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        );

    }

    private function createEllipsisPage()
    {

        return array(
            'num'       => '...',
            'url'       => NULL,
            'isCurrent' => FALSE,
        );

    }

    public function getPages()
    {

        $pages = array();

        if ($this->totalPages <= $this->pagesPerBlock)
        {
            for ($i = 1; $i <= $this->totalPages; $i++)
            {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
        }
        else
        {
            if(TRUE === $this->viewStartEnd)
            {
                $pagesPerBlock = $this->pagesPerBlock - 2;
            }
            else
            {
                $pagesPerBlock = $this->pagesPerBlock;
            }
            $numAdjacents = (int)floor(($pagesPerBlock - 1) / 2);

            if ($this->currentPage + $numAdjacents > $this->totalPages)
            {
                $startPage = $this->totalPages - $pagesPerBlock+1;// + 2;
            }
            else
            {
                $startPage = $this->currentPage - $numAdjacents;
            }
            if ($startPage < 1)
            {
                $startPage = 1;
            }
            $endPage = $startPage + $pagesPerBlock - 1;
            if ($endPage >= $this->totalPages)
            {
                $endPage = $this->totalPages;
            }

            if(TRUE === $this->viewStartEnd && $startPage >1)
            {
                $pages[] = $this->createPage(1, $this->currentPage == 1);
				if($startPage>2){
                $pages[] = $this->createEllipsisPage();
				}
            }
            for ($i = $startPage; $i <= $endPage; $i++)
            {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
            if(TRUE === $this->viewStartEnd && $endPage < $this->totalPages -1)
            {
                $pages[] = $this->createEllipsisPage();
                $pages[] = $this->createPage($this->totalPages, $this->currentPage == $this->totalPages);
            }
        }

        return $pages;

    }

    public function toArray()
    {

        return array(
            'totalPages'  => $this->totalPages,
            'currentPage' => $this->currentPage,
            'prevUrl'     => $this->getPageUrl($this->prevPage),
            'prevPage' => $this->prevPage,
            'pages'       => $this->getPages(),
            'nextUrl'     => $this->getPageUrl($this->nextPage),
            'nextPage' => $this->nextPage,
        );

    }

    public function toHtml()
    {

        global $langPrefix, $m;
		
		$paginator = $this->toArray();
		
		if($m){
			
        $html = '<center><ul class="pagination">';
        if ($paginator['prevUrl'])
        {
            $html .= '<li class="pgnextprev"><a href="' .$langPrefix. $paginator['prevUrl'] . '"'.(($this->is_searching==true)?' onclick="runSearching($(this).attr(\'href\'), true); return false;"':'').'>'.l('prev_page').'</a></li>';
        }
        if ($paginator['nextUrl'])
        {
            $html .= '<li class="pgnextprev"><a href="' .$langPrefix . $paginator['nextUrl'] . '"'.(($this->is_searching==true)?' onclick="runSearching($(this).attr(\'href\'), true); return false;"':'').'>'.l('next_page').'</a></li>';
        }
        $html .= '</ul></center>';
			
		} else {

        $html = '<ul class="pagination">';
        if ($paginator['prevUrl'])
        {
            $html .= '<li class="pgnextprev"><a href="' .$langPrefix. $paginator['prevUrl'] . '"'.(($this->is_searching==true)?' onclick="runSearching($(this).attr(\'href\'), true); return false;"':'').'>'.l('prev_page').'</a></li>';
        }
        foreach ($paginator['pages'] as $page)
        {
            if ($page['url'])
            {
                $html .= '<li' . ($page['isCurrent'] ? ' class="active"' : '') . '><a href="' .$langPrefix. $page['url'] . '"'.(($this->is_searching==true)?' onclick="runSearching($(this).attr(\'href\'), true); return false;"':'').'>' . $page['num'] . '</a></li>';
            }
            else
            {
                $html .= '<li class="disabled"><span>' . $page['num'] . '</span></li>';
            }
        }
        if ($paginator['nextUrl'])
        {
            $html .= '<li class="pgnextprev"><a href="' .$langPrefix . $paginator['nextUrl'] . '"'.(($this->is_searching==true)?' onclick="runSearching($(this).attr(\'href\'), true); return false;"':'').'>'.l('next_page').'</a></li>';
        }
        $html .= '</ul>';
		
		}

        return $html;

    }

}