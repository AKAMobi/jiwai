<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 */

/**
 * JiWai.de Pagination Class
 */
class JWPagination {
	/**
	 * Instance of this singleton
	 *
	 * @var JWPagination
	 */
	private $mNumPerPage;

	private $mTotalNum;

	/**
	 * Constructing method, save initial state
	 *
	 *	@param	int	$total_num	分页元素的总数
	 *	@param	int	$page_no	当前页，第一页是 1
	 */
	function __construct($total_num, $page_no)
	{
		$total_num 	= intval($total_num);
		$page_no	= intval($page_no);

		// 我们从第一页开始算，没有老师说“请翻开课本的第0页”……
		if ( 0==$page_no )
			$page_no = 1;

		$this->mTotalNum 		= $total_num;
		$this->mCurrentPageNo 	= $page_no;

		// 每页显示的条目数
		$this->mNumPerPage		= 20;
	}

	public function IsShowNewest()
	{
		if ( $this->mCurrentPageNo > 1 )
			return true;

		return false;
	}

	public function GetNewestPageNo()
	{
		return 1;
	}

	public function IsShowNewer()
	{
		if ( $this->mCurrentPageNo > 2 )
			return true;

		return false;
	}

	public function GetNewerPageNo()
	{
		return $this->mCurrentPageNo - 1;
	}

	public function IsShowOlder()
	{
		if ( $this->mCurrentPageNo <  $this->GetOldestPageNo()-2 )
			return true;

		return false;
	}

	public function GetOlderPageNo()
	{
		return $this->mCurrentPageNo + 1;
	}

	public function IsShowOldest()
	{
		if ( $this->mCurrentPageNo < $this->GetOldestPageNo() )
			return true;

		return false;
	}

	public function GetOldestPageNo()
	{
		return ceil($this->mTotalNum/$this->mNumPerPage);
	}

	public function GetNumPerPage()
	{
		return $this->mNumPerPage;
	}

	public function GetStartPos()
	{
		return ($this->mCurrentPageNo - 1)*$this->mNumPerPage;
	}
}