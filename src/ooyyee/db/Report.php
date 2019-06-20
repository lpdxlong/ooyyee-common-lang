<?php

namespace ooyyee\db;


use ooyyee\Excel;
use ooyyee\TableColumnParser;
use think\db\Query;



class Report {

	
	private $column; // pk
	private $dataProcessor;
	private $chunkSize=50;
	private $query;
	private $completeProcessor;
	private $dataCount=0;
	private $dataList=[];
	private $titles=[];
	private $keys=[];
	private $title;
	private $sortFunction;

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }



	/**
	 * @return callable $completeProcessor
	 */
	public function getCompleteProcessor() {
		return $this->completeProcessor;
	}

	/**
	 * @param callable $completeProcessor
	 */
	public function setCompleteProcessor($completeProcessor) {
		$this->completeProcessor = $completeProcessor;
	}

	/**
	 * @return Query $query
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @param Query $query
	 */
	public function setQuery($query) {
		$this->query = $query;
	}

	/**
	 * @return array $column
	 */
	public function getColumn() {
		return $this->column;
	}


	/**
	 * @return int $chunkSize
	 */
	public function getChunkSize() {
		return $this->chunkSize;
	}

	/**
	 * @param string $column
	 */
	public function setColumn($column) {
		$this->column = $column;
	}


	/**
	 * @param int $chunkSize
	 */
	public function setChunkSize($chunkSize) {
		$this->chunkSize = $chunkSize;
	}

	
	
	/**
	 * @return callable $dataProcessor
	 */
	public function getDataProcessor() {
		return $this->dataProcessor;
	}

	/**
	 * @param callable $dataProcessor
	 */
	public function setDataProcessor($dataProcessor) {
		$this->dataProcessor = $dataProcessor;
	}

	/**
	 * @return int $dataCount
	 */
	public function getDataCount() {
		return $this->dataCount;
	}

	/**
	 * @param number $dataCount
	 */
	public function setDataCount($dataCount) {
		$this->dataCount = $dataCount;
	}
	/**
	 * @param number $dataCount
	 */
	public function incDataCount($dataCount){
		$this->dataCount+=$dataCount;
	}

	private function processColumn($columns){
        $_columns=TableColumnParser::parse($columns);
		return empty($_columns)?$columns:$_columns;
	}
	public function sort(Callable $sortFunction){
	    $this->sortFunction=$sortFunction;
    }
	/**
	 * 
	 * @param array $columns
	 * @param string $filename
	 * @return int
     * @throws
	 */
	public function build($columns ,$filename){
	    $this->setTitle('导出'.$filename);
		$columns=$this->processColumn($columns);

		try{
			$this->setColumns($columns);
			$this->getQuery()->chunk($this->getChunkSize(), function($dataList) use(&$totalDataList){
				$dataList=call_user_func($this->dataProcessor,$dataList);

				return $this->processData($dataList);
			},$this->getColumn());
			if($this->getCompleteProcessor()){
				call_user_func($this->getCompleteProcessor(),$this->dataList,count($this->dataList));
			}
			if(is_callable($this->sortFunction)){
			    $this->dataList=call_user_func($this->sortFunction,$this->dataList);
            }
			$reportData=[];
            foreach ($this->dataList as $v){
                $row=[];
                foreach ($this->keys as $k){
                    $row[]=$v[$k];
                }
                $reportData[]=$row;
            }

            Excel::report(array_merge([$this->titles],$reportData), $filename);
			return count($reportData);
		}catch(\Exception $e){
			header_remove('Content-type');
			header_remove('Content-Disposition');
			header_remove('Pragma');
			header_remove('Expires');
			throw $e;
		}
	}
	
	
	/**
	 * @param array: $columns
	 */
	private function setColumns($columns) {
	    $this->titles=array_map(function($column){
			return preg_replace('/\(.*?\)/', '', $column['title']);
		}, array_values($columns));
		$this->keys = array_column($columns,'field');
	}
	
	private function processData($dataList){
		foreach ($dataList as $v){
		    $this->dataList[]=$v;
		}
		$count=count($dataList);
        return !($count<$this->getChunkSize());

	}
}

?>