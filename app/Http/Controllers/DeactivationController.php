<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;

class DeactivationController extends Controller
{
    /**
     * This function will deactivate locally and remotely
     * @param Request $request
     * @return void
     */
    public function deactivate(Request $request){
        $customers = $request->customer;
        //Initialise a client to work with HTTP Request
        $client = new Client(['cookies' => true]);
        //Gets the login page and logs in to website
        $loginPage = $this->getLogin($client);
        $tokens = $this->extractTokens($loginPage);
        $login = $this->postLogin($tokens,$client);

        foreach ($customers as $id){
            $customer = DB::table('customers')->where('customers_id','=',$id)->first();
            $subsid = $customer->subsid;
            $stbid = $customer->stbid;
            //Post disconnection
            $url = "http://103.217.84.186/dlgtpl/Admin/frmcassubsstatus.aspx?stbid=". $stbid . "&subsid=" . $subsid . "&Type=Y&b=0";
            $deactivationPage = $this->getCustomerPage($url,$client);
            $pageTokens = $this->extractTokens($deactivationPage);
            $postDeactivation = $this->postDisconnect($url,$pageTokens,$client);
            //Updates the database locally.
            DB::table('customers')->where('customers_id','=',$id)->update(['setTopBoxStatus' => 'Disconnected']);
        }
        return redirect()->back()->with('status','Successfully disconnected ' . count($customers) . " customers");
    }

    /**
     * Below are the functions which will
     * deactivate the records on the remote server
     * by making a post request to server with appropiate 
     * parameters
     */

     /**
      * Gets the login page to
      * extract the session tokens
      * to bypass security
      */
    public function getLogin($client){
        $url = "http://103.217.84.186/dlgtpl/Login.aspx";
        $response = $client->request('GET',$url);
        return $response->getBody();
    }

    /**
     * Extracts the token from the page
     * by parsing the html
     * @param HTML DOM
     * @return array[tokens]
     */

    public function extractTokens($html){
        $dom = new \Htmldom($html);
        $keys = array();
        foreach ($dom->find('input') as $input){
            if ($input->type == "hidden"){
                array_push($keys,['name' => $input->name,'id' => $input->id, 'value' => $input->value]);
            }
        }
        return $keys;

    }

    /**
     * This functions sends the login 
     * form to the server which is authenticated
     * by the server and stores the cookies
     * @param Tokens, Client
     * @return URL
     */
    public function postLogin($keys,$client){
        $username = "prashant";
        $password = "123456";
        $url = "http://103.217.84.186/dlgtpl/Login.aspx";
        $data = [
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            '__LASTFOCUS' => '',
            '__VIEWSTATE' => $keys[0]['value'],
            '__VIEWSTATEGENERATOR'=> $keys[1]['value'],
            '__EVENTVALIDATION' => $keys[2]['value'],
            'xTxtBxUserName' => $username,
            'xTxtBxPassword' => $password,
            'xBtnLogin.x' => 98,
            'xBtnLogin.y' => 20,
        ];
        $request = $client->post($url,array(
            'form_params' => $data
        ));
        return $request->getBody();
    }


    /**
     * Again, gets the customer page to extract tokens
     * So it can send the deactivation POST request to
     * the server.
     * @param $url
     * @return HTML DOM
     */
    public function getCustomerPage($url,$client){
        $res = $client->request('GET',$url);
        return $res->getBody();
    }

    /**
     * Request disconnection on remote server
     * This will send a POST request to remote server
     * which will them disconnect the user remotely
     * Takes in the URL, Tokens and Client
     * @param $url, $token, $client
     * @return void
     */
    public function postDisconnect($url,$token,$client){
        $date = date('d-m-Y', time());
        $data = [
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            '__VIEWSTATE' => $token[0]['value'],
            '__VIEWSTATEGENERATOR' => $token[1]['value'],
            '__EVENTVALIDATION' => $token[2]['value'],
            '_ctl0:ContentPlaceHolder1:txtStatusDate' => $date,
            '_ctl0:ContentPlaceHolder1:txtCharges' => '0',
            '_ctl0:ContentPlaceHolder1:txtRemark' => '',
            '_ctl0:ContentPlaceHolder1:btnSubmit' => 'Disconnect'

        ];
        $request = $client->post($url,array(
            'form_params' => $data
        ));
        return $request->getBody();

    }

}
