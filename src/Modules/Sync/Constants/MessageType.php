<?php

namespace SpotiSync\Modules\Sync\Constants;

final class MessageType
{
  public static $ROOM_LIST_SYNC = "ROOM_LIST_SYNC";
  public static $ROOM_CREATE = "ROOM_CREATE";
  public static $ROOM_JOIN = "ROOM_JOIN";
  public static $ROOM_SYNC = "ROOM_SYNC";
  public static $ROOM_PLAY = "ROOM_PLAY";
  public static $ROOM_PAUSE = "ROOM_PAUSE";
  public static $ROOM_ADD_QUEUE = "ROOM_ADD_QUEUE";
  public static $ROOM_PLAY_NEXT = "ROOM_PLAY_NEXT";
  public static $ROOM_TRACK_DOWNVOTE = "ROOM_TRACK_DOWNVOTE";
  public static $ROOM_TRACK_UPVOTE = "ROOM_TRACK_UPVOTE";
  public static $ROOM_DELETE = "ROOM_DELETE";
  public static $ROOM_SET_COLOR = "ROOM_SET_COLOR";
  public static $ROOM_CHAT_MESSAGE = "ROOM_CHAT_MESSAGE";

  public static $AVAILABLE_DEVICES = "AVAILABLE_DEVICES";
  public static $SET_ACTIVE_DEVICE = "SET_ACTIVE_DEVICE";
}
