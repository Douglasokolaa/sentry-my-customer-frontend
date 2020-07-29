<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class BroadcastController extends Controller
{
    protected $host;
    protected $headers;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->host = env('API_URL', 'https://dev.api.customerpay.me');
        $this->headers = ['headers' => ['x-access-token' => Cookie::get('api_token')]];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $url = env('API_URL', 'https://dev.api.customerpay.me') . '/store';

        try {

            $client = new Client;
            $payload = ['headers' => ['x-access-token' => Cookie::get('api_token')]];

            $response = $client->request("GET", $url, $payload);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $Stores = json_decode($body);

            if ($statusCode == 200) {
                // return $Stores->data->stores;
                return view('backend.broadcasts.index')->with('response', $Stores->data->stores);
            }
        } catch (RequestException $e) {
            Log::error('Catch error: Create Broadcast' . $e->getMessage());
            Session::flash('message', 'Failed to fetch customer, please try again');
            return view('backend.broadcasts.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $client = new Client();
            $response = $client->get($this->host . '/message/numbers', ['headers' => ['x-access-token' => Cookie::get('api_token')]]);
            $template = $request->input("temp");



            if ($response->getStatusCode() == 200) {

                // dd($template);
                $res = json_decode($response->getBody());
                $customers = get_object_vars($res->data);
                return view('backend.broadcasts.index')->with(['customers' => $customers, "template" => $template]);
            }
        } catch (RequestException $e) {
            Log::error('Catch error: Create Broadcast' . $e->getMessage());
            $request->session()->flash('message', 'Failed to fetch customer, please try again');
            return view('backend.broadcasts.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $client = new Client();

            if ($request->input('send_to') == 1) {
                $store = $client->get(
                    $this->host . '/store/' . $request->input('store'),
                    ['headers' => ['x-access-token' => Cookie::get('api_token')]]
                );
                $customers =  json_decode($store->getBody())->data->store->customers;
                $numbers = [];
                foreach ($customers as $customer) {
                    $numbers[] = $customer->phone_number;
                }
            } else {
                $numbers = $request->input('customer');
            }

            $message = $request->input('message');

            if ($request->input('message') == 'other') {
                $message = $request->input('_message');
            }

            $response = $client->post($this->host . '/message/send', [
                'json' => [
                    'message' => $message,
                    'numbers' => $numbers,
                ],
                'headers' => [
                    'x-access-token' => Cookie::get('api_token')
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                $request->session()->flash('alert-class', 'alert-success');
                $request->session()->flash('message', 'Broadcast message sent !');
                return back();
            }
        } catch (RequestException $e) {
            Log::error('Catch error: Create Broadcast' . $e->getMessage());
            if ($e->getCode() == 401) {
                return redirect()->route("logout");
            }
            $request->session()->flash('message', 'Ooops, failed to send broadcast, please try again');
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function template(Request $request)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
