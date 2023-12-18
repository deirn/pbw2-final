<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StatusModel;
use App\Models\UserModel;

class ViewController extends BaseController
{
    private function view(string $viewName, string $pageTitle, array $extra = [])
    {
        return view($viewName, [
            'viewName'  => $viewName,
            'pageTitle' => $pageTitle,
            ...$extra,
        ]);
    }

    public function root()
    {
        return redirect()->to('/home');
    }

    public function home()
    {
        return $this->view('home', 'Home');
    }

    public function status(int $id)
    {
        $status = model(StatusModel::class)
            ->withDeleted()
            ->find($id);

        log_message('error', var_export($status, true));

        return $this->view('status', 'Status', [
            'status' => $status,
        ]);
    }

    public function profile(string $username)
    {
        $user = model(UserModel::class)
            ->where('username', $username)
            ->first();

        return $this->view('profile/index', $username, [
            'user' => $user,
        ]);
    }
}
