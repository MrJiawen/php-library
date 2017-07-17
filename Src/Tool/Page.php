<?php

namespace CjwLibrary\Src\Tool;

class Page
{
    // 总数
    private $total;

    // 当前页数
    private $nowPage;

    // 总页数
    private $totalPage;

    // 每页的条数
    private $pageNum;

    // 当前页数的偏移量
    private $offset;

    // 当前页面的uri
    private $uri;

    // 当前页面的queryString
    private $queryString;


    /** 配置分页基本参数
     * @param $total
     * @param $PageNum
     */
    public function setPageParams($total, $PageNum)
    {
        // 1.配置参数
        $this->total = $total;

        // 2. 每页获取的数量
        $this->pageNum = $PageNum;

        // 3. 总共页数
        $this->totalPage = ceil($total / $PageNum);

        // 4. 当前页数
        $this->nowPage = (int)(empty($_REQUEST['page']) ? 1 : $_REQUEST['page']);
        $this->nowPage = $this->nowPage < 1 ? 1 : $this->nowPage;
        $this->nowPage = $this->nowPage > $this->totalPage ? $this->totalPage : $this->nowPage;

        $uriArray = explode('?', $_SERVER['REQUEST_URI']);

        // 5. 当前页数的偏移量
        $this->offset = $this->pageNum * ($this->nowPage - 1);

        // 6. 当前uri
        $this->uri = $uriArray[0];

        // 7. 当前的queryString（不包含page参数）
        array_shift($uriArray);
        $queryString = implode('?', $uriArray);
        parse_str($queryString, $queryString);
        unset($queryString['page']);
        $this->queryString = http_build_query($queryString);
        
        return $this;
    }

    /** 获取分页的基本参数
     * @return array
     */
    public function getPageParams()
    {
        return [
            'total' => $this->total,
            'nowPage' => $this->nowPage,
            'totalPage' => $this->totalPage,
            'pageNum' => $this->pageNum,
            'offset' => $this->offset,
            'uri' => $this->uri,
            'queryString' => $this->queryString,
        ];
    }

    /** 渲染bootstrap分页的html
     * @param bool $notice
     * @return string
     */
    public function render($notice = false)
    {
        $pageStr = '';

        if ($this->nowPage < 6) {
            for ($i = 1; $i < $this->nowPage; $i++) {
                $pageStr .= $this->constructPage($i);
            }
        } else {
            $pageStr = $this->constructPage(1) . $this->constructPage(2);
            $pageStr .= $this->excessivePage() . $this->constructPage($this->nowPage - 2) . $this->constructPage($this->nowPage - 1);
        }

        $pageStr .= $this->constructPage($this->nowPage);

        if ($this->nowPage > $this->totalPage - 5) {
            for ($i = $this->nowPage + 1; $i <= $this->totalPage; $i++) {
                $pageStr .= $this->constructPage($i);
            }
        } else {
            $pageStr .= $this->constructPage($this->nowPage + 1) . $this->constructPage($this->nowPage + 2) . $this->excessivePage();
            $pageStr .= $this->constructPage($this->totalPage - 1) . $this->constructPage($this->totalPage);
        }
        $html = '
            <nav aria-label="Page navigation" style="text-align: center;"> 
              <ul class="pagination">
                <li class="' . ($this->nowPage == 1 ? 'disabled' : '') . '">
                  <a href="' . ($this->uri . '?page=' . ($this->nowPage - 1) . (empty($this->queryString) ? '' : '&' . $this->queryString)) . '" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                  </a>
                </li>
                    ' . $pageStr . '
                <li  class="' . ($this->nowPage == $this->totalPage ? 'disabled' : '') . '">
                  <a href="' . ($this->uri . '?page=' . ($this->nowPage + 1) . (empty($this->queryString) ? '' : '&' . $this->queryString)) . '" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                  </a>
                </li>
              </ul>
            </nav>';

        if ($notice)
            $html .=
                '<p>
                当前第' . $this->nowPage . '页 / 共' . $this->totalPage . '页数据 /  共 ' . $this->total . '条数据
            </p>';

        return $html;
    }

    /** 每一页的分页html设置
     * @param $nowPage
     * @return string
     * @author chenjiawen
     */
    private function constructPage($nowPage)
    {
        return
            '<li class="' . ($this->nowPage == $nowPage ? 'active' : '') . '">
                <a href="' . ($this->uri . '?page=' . $nowPage . (empty($this->queryString) ? '' : '&' . $this->queryString)) . '">' . $nowPage . '</a>
            </li>';
    }

    /** 设置过度分页
     * @return string
     * @author chenjiawen
     */
    private function excessivePage()
    {
        return
            '<li class="disabled">
                <a href="#">...</a>
            </li>';
    }
}
