<?php

namespace SpotiSync\ViewModels;

use SpotiSync\Models\User;

class UserViewModel
{
  public function __construct(User $user)
  {
    $this->id = $user->id;
    $this->name = $user->name;
    $this->online = $user->online;

    $this->spotifyId = $user->spotifyId;
  }

  public $id;
  public $name;
  public $online;

  public $spotifyId;
}