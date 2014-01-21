<?php
/**
 * Pagination example: Paginator building and rendering
 *
 * This builds a paginator to GEL spec: the list of pages is truncated to
 * either side if there is not enough space to display two at the edges,
 * one at the side of current, and the full list of intervening pages.
 *
 * @author Imran Nazar <tf@imrannazar.com>
 */

class PaginatorModel
{
    // Fire up truncated view if we get past this many pages
    const MAX_PAGES = 11;

    private $root;
    private $pagecount;
    private $curpage;
    private $links;

    /**
     * Class constructor
     * @param root string Root URL to use for paging links
     * @param pagecount int Total number of pages
     * @param curpage int Current page number
     * @throws bsException if no pages, or current page out of range
     */
    public function __construct($root, $pagecount, $curpage)
    {
        if (!$pagecount) {
            throw new bsException('No pages');
        }
        if ($curpage < 1 || $curpage > $pagecount) {
            throw new bsException('Current page out of range');
        }

        $this->root = $root;
        $this->pagecount = $pagecount;
        $this->curpage = $curpage;

        $this->links = array();
    }

    /**
     * Render the paginator into HTML
     * @return string HTML set of list items
     */
    public function render()
    {
        if (bsFactory::get('config')->paginate_show_prev_next) {
            $this->links[] = array(
                'link' => ($this->curpage == 1) ? '' : ($this->curpage - 1),
                'text' => 'Previous',
                'classes' => array(
                    'previous',
                    ($this->curpage == 1) ? 'disabled' : 'enabled'
                )
            );
        }

        $i = 0;
        while (++$i <= $this->pagecount) {
            // Apply truncation if we're above the standard-paging limit
            if ($this->pagecount > self::MAX_PAGES) {
                // Truncate to the left if there's a gap of more than
                // two pages to the "current" group
                if ($i == 3 && $this->curpage > 5) {
                    $this->links[] = array(
                        'link' => '',
                        'text' => '...',
                        'classes' => array('disabled')
                    );
    
                    if ($this->curpage > ($this->pagecount - 4)) {
                        $left_endpoint = $this->pagecount - 5;
                    } else {
                        $left_endpoint = $this->curpage - 1;
                    }
                    $i = $left_endpoint;
                }
    
                // Truncate to the right if there's a gap of more than
                // two pages to the "current" group
                if ($this->curpage < (self::MAX_PAGES - 5)) {
                    $right_threshold = self::MAX_PAGES - 4;
                } else {
                    $right_threshold = $this->curpage + 2;
                }
                if ($i == $right_threshold && $this->curpage < ($this->pagecount - 4)) {
                    $this->links[] = array(
                        'link' => '',
                        'text' => '...',
                        'classes' => array('disabled')
                    );
    
                    $i = $this->pagecount - 1;
                }
            }

            $this->links[] = array(
                'link' => $i,
                'text' => $i,
                'classes' => array(
                    ($this->curpage == $i) ? 'current' : 'page'
                )
            );
        }

        if (bsFactory::get('config')->paginate_show_prev_next) {
            $this->links[] = array(
                'link' => ($this->curpage == $this->pagecount) ? '' : ($this->curpage + 1),
                'text' => 'Next',
                'classes' => array(
                    'next',
                    ($this->curpage == $this->pagecount) ? 'disabled' : 'enabled'
                )
            );
        }

        $output = '';
        foreach ($this->links as $link) {
            if (count(array_intersect(array('disabled', 'current'), $link['classes']))) {
                $output .= sprintf('<li class="%s"><a name="%s"><span>%s</span></a></li>',
                    join(' ', $link['classes']),
                    $link['link'],
                    $link['text']
                );
            } else {
                $output .= sprintf('<li class="%s"><a href="%s/page/%s"><span data-hover="%s">%s</span></a></li>',
                    join(' ', $link['classes']),
                    $this->root,
                    $link['link'],
                    $link['text'],
                    $link['text']
                );
            }
        }

        return $output;
    }
}

