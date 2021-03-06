<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@jiwai.de
 */

/**
 * JiWai.de JWFollowRecursion Class
 */
class JWFollowRecursion{
	/**
	 * 获得级联follower_ids
	 */
	static public function GetSuperior($idUser, $level=1, $sup=true)
	{
		$sField = $sup===true ? 'idUserSuperior' : 'idUser';
		$cField = $sup===true ? 'idUser' : 'idUserSuperior';

		$user_ids = array( $idUser );
		settype( $idUser, 'array' );

		$in_ids = $idUser;
		for( $i=0; $i<$level && false==empty($in_ids) ; $i++ ) {
			$inCondition = implode( ',', $in_ids );
			$sql = "SELECT $sField FROM FollowRecursion WHERE $cField in ($inCondition)";
			$rows = JWDB::GetQueryResult( $sql, true );
			if( empty($rows ) )
				break;

			$in_ids = array();
			foreach( $rows as $r ) {
				array_push( $in_ids, $r[ $sField ] );
				array_push( $user_ids, $r[ $sField ] );
			}
		}

		$user_ids = array_unique( $user_ids );
		return $user_ids;
	}

	/**
	 * 销毁级联关系
	 */
	static public function Destroy( $idUser, $idUserSuperior )
	{
		$f = func_get_args();

		$idUserSuperior = JWDB::CheckInt( $idUserSuperior );
		$idUser = JWDB::CheckInt( $idUser );

		$eArray = array( 'idUser' => $idUser, 'idUserSuperior' => $idUserSuperior, );
		if ( $idExist = JWDB::ExistTableRow( 'FollowRecursion', $eArray ) ){
			return JWDB::DelTableRow( 'FollowRecursion', array( 'id' => $idExist ) );
		}

		return true;
	}
	
	/**
	 * 建立用户级联关系
	 */
	static public function Create($idUser, $idUserSuperior, $noneReverse=false)
	{
		$idUserSuperior = JWDB::CheckInt( $idUserSuperior );
		$idUser = JWDB::CheckInt( $idUser );

		if( $noneReverse === true ) {
			$e = array( 'idUser' => $idUserSuperior, 'idUserSuperior' => $idUser, );
			if( $idExist = JWDB::ExistTableRow( 'FollowRecursion', $e ) ) {
				JWDB::DelTableRow( 'FollowRecursion', array('id'=>$idExist,) );
			}
		}

		$u = array( 'idUserSuperior' => $idUserSuperior, 'idUser' => $idUser, );
		if( $idExist = JWDB::ExistTableRow( 'FollowRecursion', $u ) )
			return $idExist;

		return JWDB::SaveTableRow( 'FollowRecursion', $u );
	}
}
?>
