<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;


class RoleController extends Controller
{
    public function index (){   //index is just meant to populate your table, readall is an index
        try{
            $roles = Role::all(); // to get all the roles, calling everything from the roles table

            if ($roles->count()>0 ){ //if the number of roles we got is more than 0
                return response () -> json ( //you wanna convert the response to json  (way of formatting your data)
                     $roles         //json is also a function with parameters
                );
            }
            else {
                return "No Role was Found";
            }

        }
        catch (\Exception $e) {   //implements say when laravel did not understand your logic
            return response()-> json ([
                "Error"=>"Error fetching Roles"
            ],500 );  // we want the programmer to understand the error (You wanna tell laravel to explicitly tell you what the problem is)
        } //401 and 403 are to help confuse hackers (so that they don't know the exact problem)
    }

    public function createRole(Request $request){  // Need to validate first
        $validated = $request -> validate ([    //This is a variable being assigned the name validated
            "name"=>"required|string|max:255|unique:roles",  //Unique for the roles table
            "slug"=>"required|string|max:255|unique:roles",
            "description"=>"nullable|string|max:1000"
        ]);

        try {
            $role = new Role();  // you create an instance of a role, so you have to import the role model
            $role->name=$request->name;  //same as $role->name=$validated ['name']
            $role->slug=$request->slug;
            $role->description=$request->description;

            $createdRole= $role -> save();  //same as - Role::create($role);

            if ($createdRole){
                return "Role Created Successfully";
            }
            else {
                return "Role not created";
            }

        }
        catch (\Exception $e) {
            return response()-> json ([
                "Error"=>"Error creating a role"
            ],500 );                             //If you see this error it means createRole() is failing
        }

    }

    public function getRole($id){
        // try{
            $fetchedRole = Role::findOrFail($id);  //doesn't need a try and catch if you're using findorfail

            if ($fetchedRole -> count()>0){
                return response()->json([$fetchedRole], 200);
            }
            else {
                return "Role was not Found for ID: `$id`";
            }
        // }
        // catch (\Exception $e)  {
        //     return response()->json([
        //         "Error"=>"Error Fetching Role '$e'"
        //     ], 401);
        // }
    }

    public function updateRole(Request $request, $id){  //the 3-step process - fetch, check, return
        $validated = $request -> validate ([    //Validated whatever is coming in
            "name"=>"required|string|max:255|unique:roles",  //Unique for the roles table
            "slug"=>"required|string|max:255|unique:roles",
            "description"=>"nullable|string|max:1000"
        ]);


        $roleToUpdate = Role::findOrFail($id);

        if($roleToUpdate){
            $roleToUpdate->name = $validated['name'];
            $roleToUpdate->slug = $validated['slug'];
            $roleToUpdate->description = $validated['description'];
        }

        try{
            $updatedRole = $roleToUpdate -> save();
            if ($updatedRole){
                return response ()->json($updatedRole);
            }
            else {
                return "Role not Update";
            }
        }
        catch (\Exception $e) {
            return response()-> json ([
                "Error"=>"Error creating a role"
            ],500 );                             //If you see this error it means createRole() is failing
        }




        // try{
        //  // to get all the roles
        //     $roleToUpdate->name = $request->name;
        //     $roleToUpdate->slug = $request->slug;
        //     $roleToUpdate->description = $request->description;

        //     $updatedRole = $roleToUpdate->save($validated);

        //     if ($updatedRole){
        //         return response () -> json ([ //you wanna convert the response to json
        //              $role
        //         ], 201);
        //     }
        //     else {
        //         return "Role was not Found";
        //     }

        // }
        // catch (\Exception $e) {
        //     return response ()-> json ([
        //         "Error"=> "Error Updating Role '$e'"
        //     ], 401);

        // }
    }

    public function deleteRole ($id){

        try {
        $roleToDelete = Role::findOrFail($id);

        if($roleToDelete){
                $deletedRole = Role::destroy($id);

                if ($deletedRole){
                    return "Role Deleted Successfully";
                }

                else {
                    return "Failed to Delete Role";
                }
            }

        }
        catch (\Exception $e) {
            return response()-> json ([
                "Error"=>"Error deleting role"
            ],500 );                             //If you see this error it means createRole() is failing
        }
        }

}
