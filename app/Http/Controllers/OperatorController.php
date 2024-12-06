<?php

namespace App\Http\Controllers;

use App\Location;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class OperatorController extends HomeController
{
    public function show()
    {
        try {
            $query = User::leftJoin('locations as l', 'users.destination_id', '=', 'l.id')
                ->leftJoin('roles as r', 'users.role_id', '=', 'r.id')
                ->leftJoin('filial as br', 'users.branch_id', '=', 'br.id')
                ->whereNotIn('users.role_id', [2]) //client
                ->whereNull('users.deleted_by');

            if (Auth::id() != 1) {
                $query->where('users.id', '<>', 1);
            }

            if (Auth::id() != 138869) {
                $query->where('users.id', '<>', 138869);
            }

            if (Auth::id() != 131536) {
                $query->where('users.id', '<>', 131536);
            }

            $search_arr = array(
                'name' => '',
                'surname' => '',
                'phone' => '',
                'email' => '',
                'username' => '',
                'role' => '',
                'location' => '',
            );

            if (!empty(Input::get('name')) && Input::get('name') != '' && Input::get('name') != null) {
                $where_name = Input::get('name');
                $query->where('users.name', 'LIKE', '%' . $where_name . '%');
                $search_arr['name'] = $where_name;
            }

            if (!empty(Input::get('surname')) && Input::get('surname') != '' && Input::get('surname') != null) {
                $where_surname = Input::get('surname');
                $query->where('users.surname', 'LIKE', '%' . $where_surname . '%');
                $search_arr['surname'] = $where_surname;
            }

            if (!empty(Input::get('phone')) && Input::get('phone') != '' && Input::get('phone') != null) {
                $where_phone = Input::get('phone');
                $query->where('users.phone1', 'LIKE', '%' . $where_phone . '%');
                $search_arr['phone'] = $where_phone;
            }

            if (!empty(Input::get('email')) && Input::get('email') != '' && Input::get('email') != null) {
                $where_email = Input::get('email');
                $query->where('users.email', 'LIKE', '%' . $where_email . '%');
                $search_arr['email'] = $where_email;
            }

            if (!empty(Input::get('username')) && Input::get('username') != '' && Input::get('username') != null) {
                $where_username = Input::get('username');
                $query->where('users.username', 'LIKE', '%' . $where_username . '%');
                $search_arr['username'] = $where_username;
            }

            if (!empty(Input::get('role')) && Input::get('role') != '' && Input::get('role') != null) {
                $where_role = Input::get('role');
                $query->where('users.role_id', $where_role);
                $search_arr['role'] = $where_role;
            }

            if (!empty(Input::get('location')) && Input::get('location') != '' && Input::get('location') != null) {
                $where_location = Input::get('location');
                $query->where('users.destination_id', $where_location);
                $search_arr['location'] = $where_location;
            }

            //short by start
            $short_by = 'users.id';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != '' && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != '' && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish

            $operators = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'users.id',
                    'users.name',
                    'users.surname',
                    'users.username',
                    'users.first_pass',
                    'users.email',
                    'users.phone1',
                    'users.destination_id',
                    'users.role_id',
                    'l.name as location',
                    'r.role',
                    'users.created_at',
                    'users.branch_id',
                    'br.name as branch_name'
                )
                ->paginate(50);

            $locations = Location::whereNull('deleted_by')->select('id', 'name')->get();
            $roles = Role::whereNull('deleted_by')->whereNotIn('id', [2])->select('id', 'role')->get();
            $branchs = DB::table('filial')->where('is_active', 1)->get();

            return view("backend.operators", compact(
                'operators',
                'locations',
                'roles',
                'search_arr',
                'branchs'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            if (Auth::id() != 1 && $request->id == 1) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
            }

            User::where(['id' => $request->id])->whereNotIn('role_id', [2])->whereNull('deleted_by')->update(['deleted_by' => Auth::id(), 'deleted_at' => Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id' => $request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone1' => ['required', 'string', 'max:30', 'unique:users'],
            'destination_id' => ['required', 'integer'],
            'role_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            unset($request['id']);
            $request->merge(['created_by' => Auth::id()]);

            $old_password = $request->password;

            $new_password = Hash::make($old_password);
            unset($request['password']);
            $request['password'] = $new_password;

            if (Auth::user()->role() != 2) {
                $request->merge(['first_pass' => $old_password]);
            }

            User::create($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $request->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->id],
            'phone1' => ['required', 'string', 'max:30'],
            'destination_id' => ['required', 'integer'],
            'role_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            if (Auth::id() != 1 && $request->id == 1) {
                return response(['case' => 'warning', 'title' => 'Opps!', 'content' => 'Access denied!']);
            }

            $id = $request->id;
            unset($request['id'], $request['_token']);

            if (empty($request->password) || !isset($request->password)) {
                unset($request['password']);
            } else {
                if (Auth::user()->role() != 2) {
                    $request->merge(['first_pass' => $request->password]);
                }

                $new_password = Hash::make($request->password);
                unset($request['password']);
                $request['password'] = $new_password;
            }

            User::where(['id' => $id])->whereNotIn('role_id', [2])->whereNull('deleted_by')->update($request->all());

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
}
