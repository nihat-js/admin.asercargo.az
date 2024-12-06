<?php

namespace App\Http\Controllers;

use App\LoginLog;
use App\Role;
use Illuminate\Support\Facades\Input;

class UsersLogController extends HomeController
{
    public function get_logs() {
        try {
            $search_arr = array(
                'users_type' => '',
                'user' => '',
                'role' => '',
                'type' => '',
                'start_date' => '',
                'end_date' => '',
            );

            $query = LoginLog::leftJoin('users', 'login_log.user_id', '=', 'users.id')
                ->leftJoin('roles', 'login_log.role_id', '=', 'roles.id');

            if (!empty(Input::get('users_type')) && Input::get('users_type') != ''  && Input::get('users_type') != null) {
                $where_users_tye = Input::get('users_type');
                switch ($where_users_tye) {
                    case 'staff': {
                        $query->where('login_log.role_id', '<>', 2);
                    } break;
                    case 'client': {
                        $query->where('login_log.role_id', 2);
                    } break;
                }
                $search_arr['users_type'] = $where_users_tye;
            }

            if (!empty(Input::get('user')) && Input::get('user') != ''  && Input::get('user') != null) {
                $where_user_id = Input::get('user');
                $query->where('login_log.user_id', $where_user_id);
                $search_arr['user'] = $where_user_id;
            }

            if (!empty(Input::get('role')) && Input::get('role') != ''  && Input::get('role') != null) {
                $where_role_id = Input::get('role');
                $query->where('login_log.role_id', $where_role_id);
                $search_arr['role'] = $where_role_id;
            }

            if (!empty(Input::get('type')) && Input::get('type') != ''  && Input::get('type') != null) {
                $where_type = Input::get('type');
                $query->where('login_log.type', $where_type);
                $search_arr['type'] = $where_type;
            }

            if (!empty(Input::get('start_date')) && Input::get('start_date') != ''  && Input::get('start_date') != null) {
                $where_start_date = Input::get('start_date');
                $query->where('login_log.created_at', '>=', $where_start_date);
                $search_arr['start_date'] = $where_start_date;
            }

            if (!empty(Input::get('end_date')) && Input::get('end_date') != ''  && Input::get('end_date') != null) {
                $where_end_date = Input::get('end_date');
                $query->where('login_log.created_at', '<=', $where_end_date);
                $search_arr['end_date'] = $where_end_date;
            }

            //short by start
            $short_by = 'login_log.id';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != ''  && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != ''  && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish

            $logs = $query->orderBy($short_by, $shortType)
                ->select('login_log.id', 'login_log.user_id', 'users.username', 'roles.role', 'login_log.type', 'login_log.ip', 'login_log.created_at')
                ->paginate(50);

            $roles = Role::whereNull('deleted_by')->select('id', 'role')->get();

            return view('backend.admin.users_logs', compact('logs', 'roles', 'search_arr'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }
}
