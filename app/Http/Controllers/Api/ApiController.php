<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function register(Request $request){
        $request->validate([
            "name"      => "required",
            "email"     => "required|email|unique:users",
            "password"  => "required|confirmed"
        ]);

        User::create([
            "name"      => $request->name,
            "email"     => $request->email,
            "password"  => Hash::make($request->password)
        ]);

        return response()->json([
            "status"    => "success",
            "message"   => "User created successfully"
        ]);

    }

    public function login(Request $request){
        $request->validate([
            "email"     => "required|email",
            "password"  => "required"
        ]);

        $token = JWTAuth::attempt([
            'email'     => $request->email,
            'password'  => $request->password
        ]);

        if(!empty($token)){
            return response()->json([
                'status'    => 'success',
                'message'   => 'User logged in successfully',
                'token'     => $token
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Invalid login details'
        ]);
    }

    public function profile(){

        $userData = auth()->user();

        $tasks = [
            "completed" => Task::where("user_id", $userData->id)->where("status",1)->count(),
            "pending"   => Task::where("user_id", $userData->id)->where("status",0)->count()
        ];
        $userData['tasks'] = $tasks;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Profile data',
            'user'      => $userData
        ]);

    }

    public function refreshToken(){

        $newToken = auth()->refresh();

        return response()->json([
            'status'    => 'success',
            'message'   => 'New token generated successfully',
            'token'     => $newToken
        ]);

    }

    public function logout(){

        auth()->logout();

        return response()->json([
            'status'    => 'success',
            'message'   => 'User logout successfully'
        ]);

    }

    public function getTasks(Request $request){
        $task = Task::query();
        $page_size = 10;
        if($request->query('pageSize')){
            $page_size = $request->query('pageSize');
        }
        if($request->query('title')){
            $task->where('title', 'like','%'.$request->query('title').'%');
        }
        if($request->query('status')){
            $task->where('status', $request->query('status'));
        }
        if($request->query('due_date')){
            $task->whereDate('due_date', '<=', Carbon::parse($request->query('due_date'))->format('Y-m-d'));
        }

        return response()->json([
            'status'=> 'success',
            'data'  => $task->where('user_id', auth()->id())->orderBy('due_date')->paginate($page_size)
        ]);
    }

    public function createTask(Request $request){
        $request->validate([
            'title'         => 'required',
            'description'   => 'required',
            'due_date'      => 'required',
            'status'        => 'required',
        ]);
        Task::create([
            'user_id'       => auth('')->id(),
            'title'         => $request->title,
            'description'   => $request->description,
            'due_date'      => Carbon::parse($request->due_date)->format('Y-m-d'),
            'status'        => $request->status,
        ]);
        return response()->json([
            'status'    => 'success',
            'message'   => 'Task created successfully'
        ]);
    }

    public function deleteTask(Request $request){
        $request->validate( ['id' => 'required']);


        Task::where('id', $request->id)->where('user_id', auth()->id())->delete();

        return response()->json([
            'status' => 'success',
            'message'=> 'Task deleted successfully'
        ]);
    }

    public function updateTask(Request $request){
        $request->validate([
            'id'            => 'required',
            'title'         => 'required',
            'description'   => 'required',
            'due_date'      => 'required',
            'status'        => 'required',
        ]);
        $task = Task::where('id', $request->id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

        $task->update([
            'title'         => $request->title,
            'description'   => $request->description,
            'due_date'      => Carbon::parse($request->due_date)->format('Y-m-d'),
            'status'        => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message'=> 'Task updated successfully'
        ]);

    }
}
