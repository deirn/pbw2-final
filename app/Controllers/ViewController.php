<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StatusModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Mpdf\Mpdf;

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

    public function search()
    {
        return $this->view('search', 'Search');
    }

    public function info()
    {
        return $this->view('info', 'Info');
    }

    public function status(int $id)
    {
        $status = model(StatusModel::class)
            ->withDeleted()
            ->find($id);

        if ($status == null) {
            throw PageNotFoundException::forPageNotFound('Unknown Status');
        }

        return $this->view('status', 'Status', [
            'status' => $status,
        ]);
    }

    public function profile(string $username)
    {
        $user = model(UserModel::class)
            ->where('username', $username)
            ->first();

        $profile = $user?->getProfile();

        if ($profile == null) {
            throw PageNotFoundException::forPageNotFound('Unknown Profile');
        }

        return $this->view('profile/index', $username, [
            'user'    => $user,
            'profile' => $profile,
        ]);
    }

    public function profile_settings()
    {
        return $this->view('profile/settings', 'Profile Settings', [
            'error'       => session('error') ?? [],
            'initProfile' => session('initProfile') ?? false,
        ]);
    }

    private function _connection(string $username, bool $following)
    {
        $user = model(UserModel::class)
            ->where('username', $username)
            ->first();

        if ($user == null) {
            throw PageNotFoundException::forPageNotFound('Unknown Profile');
        }

        return $this->view('profile/connections', $username, [
            'user'           => $user,
            'connections'    => $following ? $user->getFollowing() : $user->getFollowers(),
            'isFollowingTab' => $following,
        ]);
    }

    public function following(string $username)
    {
        return $this->_connection($username, true);
    }

    public function followers(string $username)
    {
        return $this->_connection($username, false);
    }

    public function export_connections(string $username)
    {
        $user = model(UserModel::class)
            ->where('username', $username)
            ->first();

        $profile = $user?->getProfile();

        if ($profile == null) {
            throw PageNotFoundException::forPageNotFound('Unknown Profile');
        }

        $html = view('profile/export_connections', [
            'user'    => $user,
            'profile' => $profile,
        ]);

        $this->response->setContentType('application/pdf');

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->OutputHttpInline();
    }
}
