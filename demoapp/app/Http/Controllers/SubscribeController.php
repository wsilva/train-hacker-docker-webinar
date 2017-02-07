<?php

namespace App\Http\Controllers;

use Session;
use Cache;
use Queue;

class SubscribeController extends Controller 
{
    public function index()
    {
        $qtdeSubscribed = (int) Cache::get('qtdeSubscribed');
        $data = [
            'ipaddr'=> $_SERVER['SERVER_ADDR'], 
            'hostname'=> gethostname(), 
            'qtdeSubscribed' => $qtdeSubscribed
        ];

        return view('pages.subscribe', $data);
    }

    public function Subscribed()
    {
        $input = Request::all();
        Email::create($input);

        $novoEmail = $input['email'];
        $data = ['novo-email' => $novoEmail];
        Queue::push('SendSubscribedEmails', $data);

        Session::flash('flash_message', 'E-mail adicionado, em breve na contagem!' );
        return redirect('/');
    }
}