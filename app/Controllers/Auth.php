<?php

namespace App\Controllers;

use App\Controllers\CommonController;
use App\Models\User;

class Auth extends CommonController
{
    protected $helpers = ["custom"];

    public function index()
    {
        $session = session();
        if(!$session->has("userdata")) {
            return view('sign_in');
        } else {
            return redirect("dashboard");
        }
    }

    public function submit_sign_in()
    {
        $session = session();
        $post = $this->request->getVar();
        
        $model = new User;
        $_user = $model->where("email",$post["email"])->first();
        if($_user) {
            if(is_null($_user["deleted_at"])) {
                if($_user["is_active"] == 1) {
                    if($_user["password"] == md5($post["password"])) {
                        $session->set('userdata',$_user);
                        return redirect("dashboard");
                    } else {
                        $session->setFlashData('error','Password does not match.');
                        return redirect("sign-in");
                    }
                } else {
                    $session->setFlashData('error','Your account is not active.');
                    return redirect("sign-in");
                }
            } else {
                $session->setFlashData('error','Your account is deleted.');
                return redirect("sign-in");
            }
        } else {
            $session->setFlashData('error','Email not found');
            return redirect("sign-in");
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->route('sign-in');
    }
}
