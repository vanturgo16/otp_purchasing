<?php

namespace App\Http\Controllers;

use App\Traits\AuditLogsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Browser;

// Model
use App\Models\User;

class UserController extends Controller
{
    use AuditLogsTrait;

    public function index()
    {
        $users=User::get();

        //Audit Log
        $username= auth()->user()->email; 
        $ipAddress=$_SERVER['REMOTE_ADDR'];
        $location='0';
        $access_from=Browser::browserName();
        $activity='View List Mst User';
        $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);
        
        return view('users.index',compact('users'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'role' => 'required',
        ]);

        $count= User::where('email',$request->email)->count();
        
        if($count > 0){
            return redirect()->back()->with('warning','Email Was Already Registered');
        } else {
            DB::beginTransaction();
            try{
                $users = User::create([
                    'department' => "Production",
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make('password'),
                    'is_active' => '1',
                    'role' => $request->role
                ]);

                //Audit Log
                $username= auth()->user()->email; 
                $ipAddress=$_SERVER['REMOTE_ADDR'];
                $location='0';
                $access_from=Browser::browserName();
                $activity='Create New User ('. $request->email . ')';
                $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                DB::commit();
                return redirect()->back()->with(['success' => 'Success Create New User']);
            } catch (\Exception $e) {
                dd($e);
                return redirect()->back()->with(['fail' => 'Failed to Create New User!']);
            }
        }
    }

    public function update(Request $request, $id){
        // dd($request->all());

        $iduser = decrypt($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'role' => 'required',
        ]);

        $userbefore = User::where('id', $iduser)->first();
        $userbefore->name = $request->name;
        $userbefore->email = $request->email;
        $userbefore->role = $request->role;

        if($userbefore->isDirty()){
            $count= User::where('email',$request->email)->whereNotIn('id', [$iduser])->count();
            if($count > 0){
                return redirect()->back()->with('warning','Email Was Already Registered');
            } else {
                DB::beginTransaction();
                try{
                    $users = User::where('id', $iduser)->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'role' => $request->role
                    ]);

                    //Audit Log
                    $username= auth()->user()->email; 
                    $ipAddress=$_SERVER['REMOTE_ADDR'];
                    $location='0';
                    $access_from=Browser::browserName();
                    $activity='Create New User ('. $request->email . ')';
                    $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

                    DB::commit();
                    return redirect()->back()->with(['success' => 'Success Update User']);
                } catch (\Exception $e) {
                    dd($e);
                    return redirect()->back()->with(['fail' => 'Failed to Update User!']);
                }
            }
        } else {
            return redirect()->back()->with(['info' => 'Nothing Change, The data entered is the same as the previous one!']);
        }
    }

    public function delete($id){
        $iduser = decrypt($id);

        dd($iduser);

        DB::beginTransaction();
        try{
            $users = User::where('id', $iduser)->delete();

            $name = User::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Delete User ('. $name->email . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Delete User ' . $name->email]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Delete User ' . $name->email .'!']);
        }
    }

    public function activate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = User::where('id', $id)->update([
                'is_active' => 1
            ]);

            $name = User::where('id', $id)->first();

            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Activate User ('. $name->email . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Activate User ' . $name->email]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Activate User ' . $name->email .'!']);
        }
    }

    public function deactivate($id){
        $id = decrypt($id);

        DB::beginTransaction();
        try{
            $data = User::where('id', $id)->update([
                'is_active' => 0
            ]);

            $name = User::where('id', $id)->first();
            
            //Audit Log
            $username= auth()->user()->email; 
            $ipAddress=$_SERVER['REMOTE_ADDR'];
            $location='0';
            $access_from=Browser::browserName();
            $activity='Deactivate User ('. $name->email . ')';
            $this->auditLogs($username,$ipAddress,$location,$access_from,$activity);

            DB::commit();
            return redirect()->back()->with(['success' => 'Success Deactivate User ' . $name->email]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with(['fail' => 'Failed to Deactivate User ' . $name->email .'!']);
        }
    }
}
