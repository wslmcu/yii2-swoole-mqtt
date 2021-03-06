<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2018/10/14
 * Time: 下午4:13
 */

namespace mqtt\controllers;

use immusen\mqtt\src\Controller;

class RoomController extends Controller
{

    /**
     * Verb: subscribe
     * Client want to get real time online person count, subscribe a topic e.g: room/count/100001
     * @param $room_id
     */
    public function actionCount($room_id)
    {
        //get current count
        $count = $this->redis->hget('mqtt_record_hash_#room', $room_id);
        //reply current count
        $this->publish($this->fd, $this->topic, $count ?: 0);
    }

    /**
     * Verb: publish
     * Client who join a room, send a PUBLISH to server, with a topic e.g. room/join/100001, and submit user info into $payload about somebody who join
     * also support redis pub/sub, so you can trigger this method by Yii::$app->redis->publish('async', 'room/join/100001') in your Yii Web application
     * @param $room_id
     * @param $payload
     * @return bool
     */
    public function actionJoin($room_id, $payload = '')
    {
        echo '# room ', $room_id, ' one person joined, #', $payload, PHP_EOL;
        $count = $this->redis->hincrby('mqtt_record_hash_#room', $room_id, 1);
        $sub_topic = 'room/count/' . $room_id;
        return $this->publish($this->fdsInRds($sub_topic), $sub_topic, $count);
    }

    /**
     * Verb: publish
     * similar with actionView
     * @param $room_id
     * @return bool
     */
    public function actionLeave($room_id)
    {
        $count = $this->redis->hincrby('mqtt_record_hash_#room', $room_id, -1);
        $count = $count < 1 ? 0 : $count;
        $sub_topic = 'room/count/' . $room_id;
        return $this->publish($this->fdsInRds($sub_topic), $sub_topic, $count);
    }
}