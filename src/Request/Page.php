<?php

namespace Lifetrenz\Transcendz\Request;

class Page
{
    public function __construct(
        private int $pageNumber,
        private int $recordsPerPage
    ) {
        $this->limit = $recordsPerPage;
        $this->offset = ($pageNumber - 1) * $this->limit;
    }

    private int $limit;

    private ?int $offset;

    private ?int $totalRecords;

    private int $currentPage;

    private int $totalPages;

    private int $recordsInCurrentPage;

    public function set(?int $totalRecords, int $fetchedRecords)
    {
        $this->totalRecords = $totalRecords;
        $this->currentPage = $this->totalRecords === null ? 0 : $this->pageNumber;
        $this->totalPages = $this->totalRecords / $this->recordsPerPage + ($this->totalRecords % $this->recordsPerPage > 0 ? 1 : 0);
        $this->recordsInCurrentPage = $fetchedRecords;
    }

    /**
     * Get the value of page
     *
     * @return  int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Get the value of recordsPerPage
     *
     * @return  int
     */
    public function getRecordsPerPage()
    {
        return $this->recordsPerPage;
    }

    /**
     * Get the value of limit
     *
     * @return  int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get the value of offset
     *
     * @return  int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Get the value of totalRecords
     *
     * @return  int
     */
    public function getTotalRecords()
    {
        return $this->totalRecords;
    }

    /**
     * Get the value of currentPage
     *
     * @return  int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the value of totalPages
     *
     * @return  int
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Get the value of recordsInCurrentPage
     *
     * @return  int
     */
    public function getRecordsInCurrentPage()
    {
        return $this->recordsInCurrentPage;
    }

    public function toArray()
    {
        return [
            "currentPage" => $this->getCurrentPage(),
            "totalPages" => $this->getTotalPages(),
            "totalRecords" => $this->getTotalRecords(),
            "recordsInPage" => $this->getRecordsInCurrentPage()
        ];
    }
}
