<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = Client::all();
        return $clients;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric',
            'direction' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Error de entrada",
                "erros" => $validator->errors()
            ]);
        } else {
            $client = Client::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'direction' => $request->direction,
            ]);

            return response()->json([
                "success" => true,
                "message" => "Client created successfully.",
                "data" => $client
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        $client = Client::find($client->id);
        if (is_null($client)) {
            return $this->sendError('Client not found.');
        }
        return response()->json([
            "success" => true,
            "message" => "Client retrieved successfully.",
            "data" => $client
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $client)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $client = Client::find($client->id);

        $client->name = $request['name'];
        $client->save();

        return response()->json([
            "success" => true,
            "message" => "Client updated successfully.",
            "data" => $client
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json([
            "success" => true,
            "message" => "Client deleted successfully.",
            "data" => $client
        ]);
    }
}
