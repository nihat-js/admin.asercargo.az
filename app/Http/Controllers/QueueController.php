<?php

namespace App\Http\Controllers;

use App\Location;
use App\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class QueueController extends HomeController
{
    public function show_queues() {
        try {
            $query = Queue::leftJoin('users as u', 'queue.user_id', '=', 'u.id')
                ->leftJoin('locations as l', 'queue.location_id', '=', 'l.id')
                ->whereNull('queue.deleted_by');

            $search_arr = array(
                'user' => '',
                'status' => '',
                'location' => '',
                'type' => '',
                'start_date' => '',
                'end_date' => '',
            );

            if (!empty(Input::get('user')) && Input::get('user') != ''  && Input::get('user') != null) {
                $where_user = Input::get('user');
                $query->where('queue.user_id', $where_user);
                $search_arr['user'] = $where_user;
            }

            if (!empty(Input::get('location')) && Input::get('location') != ''  && Input::get('location') != null) {
                $where_location = Input::get('location');
                $query->where('queue.location_id', $where_location);
                $search_arr['location'] = $where_location;
            }

            if (!empty(Input::get('type')) && Input::get('type') != ''  && Input::get('type') != null) {
                $where_type = Input::get('type');
                $query->where('queue.type', $where_type);
                $search_arr['type'] = $where_type;
            }

            if (!empty(Input::get('status')) && Input::get('status') != ''  && Input::get('status') != null) {
                $where_status = Input::get('status');
                $search_arr['status'] = $where_status;

                switch ($where_status) {
                    case 'used': {
                        $query->where('queue.used', 1);
                    }
                        break;
                    case 'not_used': {
                        $query->where('queue.used', 0);
                    }
                        break;
                }
            }

            if (!empty(Input::get('start_date')) && Input::get('start_date') != ''  && Input::get('start_date') != null) {
                $where_start_date = Input::get('start_date');
                $query->where('queue.date', '>=', $where_start_date);
                $search_arr['start_date'] = $where_start_date;
            }

            if (!empty(Input::get('end_date')) && Input::get('end_date') != ''  && Input::get('end_date') != null) {
                $where_end_date = Input::get('end_date');
                $query->where('queue.date', '<=', $where_end_date);
                $search_arr['end_date'] = $where_end_date;
            }

            //short by start
            $short_by = 'queue.id';
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

            $queues = $query->orderBy($short_by, $shortType)
                ->select(
                    'queue.date',
                    'queue.type',
                    'queue.no',
                    'queue.user_id',
                    'u.suite',
                    'l.name as location',
                    'queue.used_at'
                )
                ->paginate(50);

            $locations = Location::whereNull('deleted_by')->where('country_id', 1)->select('id', 'name')->get();

            return view('backend.queues', compact(
                'queues',
                'locations',
                'search_arr'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function call_next_client(Request $request) {
        $validator = Validator::make($request->all(), [
            'station' => ['required', 'integer'],
            'role' => ['nullable', 'string', 'max:15'], // information, online
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $station = $request->station;
            $operator_type = $request->role;
            $operator_id = Auth::id();
            $role = Auth::user()->role();
            $location_id = Auth::user()->location();
            $type = '';
            $queue_name = '';

            switch ($role) {
                case 4: {
                    // cashier
                    $type = 'c';
                    $queue_name = 'K';
                } break;
                case 5: {
                    // delivery
                    $type = 'd';
                    $queue_name = 'A';
                } break;
                case 7: {
                    // information or online
                    if ($operator_type == 'information') {
                        // information
                        $type = 'i';
                        $queue_name = 'I';
                    } else if ($operator_type == 'online') {
                        // online
                        $type = 'o';
                        $queue_name = 'S';
                    } else {
                        return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Operator role not found!']);
                    }
                } break;
                default: return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Queue type not found!']);
            }

            $today = Carbon::today();

            $queue = Queue::whereDate('date', $today)
                ->where(['type'=>$type, 'location_id'=>$location_id, 'used'=>0])
                ->orderBy('id', 'asc')
                ->select('id', 'no')
                ->first();

            if ($queue) {
                $queue_id = $queue->id;
                $queue_no = $queue->no;
                if ($queue_no < 100) {
                    if ($queue_no < 10) {
                        $queue_no = '00' . $queue_no;
                    } else {
                        $queue_no = '0' . $queue_no;
                    }
                }
                $queue_name .= $queue_no;

                Queue::where('id', $queue_id)->update([
                    'used' => 1,
                    'station' => $station,
                    'operator_id' => $operator_id,
                    'operator_role' => $role,
                    'used_at' => Carbon::now()
                ]);

                return response(['case' => 'success', 'title' => 'Success!', 'content' => 'The next queue: ' . $queue_name]);
            } else {
                return response(['case' => 'warning', 'title' => 'Empty!', 'content' => 'No waiting queue!']);
            }
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
}
