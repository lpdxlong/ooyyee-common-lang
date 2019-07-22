<?php

namespace ooyyee;

class CurrentUser
{
	private $user = array (
	    'id' => 0,
        'name'=>'',
	);

	public function __construct(){}

    /**
     * @param $uid
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
	public  function create($uid){

        $user = cache ( 'login_admin_' . $uid );
        if (! $user) {
            $user = db ( 'user','crm' )->where ( 'id', $uid )->field ( 'id,name,mobile,avatar' )->find ();
            $roleIds = db ( 'user_role','crm' )->where ( 'uid', $uid )->column ( 'role_id' );
            $permissions = [ ];
            if (! empty ( $roleIds )) {
                if (in_array ( 1, $roleIds,true )) {
                    $permissions = db ( 'permission_node','crm' )->column ( 'id' );
                    $user['super'] = 1;
                } else {
                    $roles = db ( 'role','crm'  )->whereIn ( 'id', $roleIds )->select ();
                    $chunks=array_map(function ($role){
                        return explode ( ',', $role['permissions']);
                    },$roles);
                    foreach ($chunks as $permission){
                        foreach ($permission as $id){
                            $permissions[]=(int)$id;
                        }
                    }
                    $permissions = array_unique ( $permissions );
                    $user['super']=0;
                }
                $user['permissions'] = array_values($permissions);
            } else {
                $user['super'] = 0;
                $user['permissions'] = [ ];
            }
            $user['roles']=$roleIds;
            cache ( 'login_admin_' . $uid, $user, 7200 );
        }
        $this->user = $user;
		return $this->user;
	}

	public  function isLogin(){
		return $this->uid() ? true : false;
	}

	public  function isSuperAdmin(){
		$user=$this->user;
		return isset ( $user['super'] ) ? $user['super'] : 0;
	}
	public  function user(){
		return $this->user;
	}
	public  function uid(){
    $user = $this->user;
    return $user['id'];
}
    public  function name(){
        $user =$this->user;
        return $user['name'];
    }
	public  function permissions(){
		$user =$this->user;
		return isset ( $user['permissions'] ) ? $user['permissions'] : [];
	}
	public  function hasRole($roleId){
        $user = $this->user;
        return isset ( $user['roles'] ) ? in_array($roleId,$user['roles'],false) : false;
    }
	public  function hasPermission($permissionId){
		$permissions=$this->permissions();
		return in_array($permissionId, $permissions,false);
	}
}

