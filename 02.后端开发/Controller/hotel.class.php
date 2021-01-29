<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Hotel {

    private $database_medoo;

    function __construct($database_medoo) {
        $this->database_medoo = $database_medoo;
    }

    //获取宾馆列表
    function GetHotelList($pageSize, $currentPage, $this_userid, $hotel_search_keywords) {
        //检查参数
        if (empty($pageSize) || empty($currentPage) || $pageSize == 0 || empty($this_userid) || !isset($hotel_search_keywords)) {
            $data['status'] = 0;
            $data['message'] = '获取宾馆列表失败 参数不完整或错误';
            return $data;
        }
        //处理关键词
        $hotel_search_keywords = "%$hotel_search_keywords%";
        //分页
        $begin = $pageSize * ($currentPage - 1);
        //操作
        $list = $this->database_medoo->select("Hotel", [
            "Hotel_id",
            "Hotel_name",
            "Hotel_city",
            "Hotel_price",
            "Hotel_rooms",
                ], [
            "AND" => [
                "OR" => [
                    "Hotel_name[~]" => $hotel_search_keywords,
                    "Hotel_price[~]" => $hotel_search_keywords,
                    "Hotel_rooms[~]" => $hotel_search_keywords,
                ],
            ],
            "LIMIT" => [$begin, $pageSize]
        ]);
        //查询预定情况
        foreach ($list as $key => $value) {
            $result = $this->database_medoo->select("Reservation_hotel", [
                "Reservation_hotel_id"
                    ], [
                "User_id" => $this_userid,
                "Hotel_id" => $value["Hotel_id"]
            ]);
            $flag = true;
            if (empty($result)) {
                $flag = false;
            }
            $list[$key]["is_reserve"] = $flag;
        }
        //计数
        $total = $this->database_medoo->count('Hotel', [
            "AND" => [
                "OR" => [
                    "Hotel_name[~]" => $hotel_search_keywords,
                    "Hotel_price[~]" => $hotel_search_keywords,
                    "Hotel_rooms[~]" => $hotel_search_keywords,
                ],
            ],
        ]);
        //处理自增的id
        $i = 0;
        foreach ($list as $key => $value) {
            $list[$key]["id"] = $i + $begin;
            $i++;
        }
        //返回
        $data['status'] = 1;
        $data['message'] = '获取宾馆列表成功';
        $data['data'] = $list;
        $data['total'] = $total;
        return $data;
    }

    //添加宾馆
    function AddHotel($Hotel_name, $Hotel_city, $Hotel_price, $Hotel_rooms) {
        //检查参数
        if (empty($Hotel_name) || empty($Hotel_city) || empty($Hotel_price) || empty($Hotel_rooms)) {
            $data['status'] = 0;
            $data['message'] = '添加失败 参数不完整或错误';
            return $data;
        }
        //检查有无重复
        $result = $this->database_medoo->select("Hotel", ["Hotel_name"], ["Hotel_name" => $Hotel_name]);
        if (!empty($result)) {
            $data['status'] = 0;
            $data['message'] = '添加失败 宾馆已存在';
            return $data;
        }
        //操作
        $this->database_medoo->insert("Hotel", [
            "Hotel_name" => $Hotel_name,
            "Hotel_city" => $Hotel_city,
            "Hotel_price" => $Hotel_price,
            "Hotel_rooms" => $Hotel_rooms,
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '添加宾馆信息成功';
        return $data;
    }

    //编辑宾馆信息
    function EditHotel($Hotel_id, $Hotel_name, $Hotel_city, $Hotel_price, $Hotel_rooms) {
        //检查参数
        if (empty($Hotel_id) || empty($Hotel_name) || empty($Hotel_city) || empty($Hotel_price) || empty($Hotel_rooms)) {
            $data['status'] = 0;
            $data['message'] = '添加失败 参数不完整或错误';
            return $data;
        }
        //操作
        $this->database_medoo->update("Hotel", [
            "Hotel_name" => $Hotel_name,
            "Hotel_city" => $Hotel_city,
            "Hotel_price" => $Hotel_price,
            "Hotel_rooms" => $Hotel_rooms
                ], [
            "Hotel_id" => $Hotel_id
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '编辑宾馆信息成功';
        return $data;
    }

    //删除宾馆信息
    function Deletehotel($Hotel_id) {
        //检查参数
        if (empty($Hotel_id)) {
            $data['status'] = 0;
            $data['message'] = '删除宾馆信息失败 参数不完整或错误';
            return $data;
        }
        //操作
        $this->database_medoo->delete("Hotel", [
            "AND" => [
                "Hotel_id" => $Hotel_id
            ]
        ]);
        $this->database_medoo->delete("Reservation_hotel", [
            "AND" => [
                "Hotel_id" => $Hotel_id
            ]
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '删除宾馆信息成功';
        return $data;
    }

    //取消和预定宾馆
    function ReserveHotel($Hotel_id, $this_userid) {
        //检查参数
        if (empty($Hotel_id) || empty($this_userid)) {
            $data['status'] = 0;
            $data['message'] = '操作失败 参数不完整或错误';
            return $data;
        }
        //查询余票
        $count = $this->database_medoo->select("Hotel", [
            "Hotel_rooms"
                ], [
            "Hotel_id" => $Hotel_id
        ]);
        $count = $count[0]["Hotel_rooms"];
        //查询是取消还是预定
        $type = $this->database_medoo->select("Reservation_hotel", ["Reservation_hotel_id"], [
            "User_id" => $this_userid,
            "Hotel_id" => $Hotel_id
        ]);
        //预定
        if (empty($type)) {
            //无房间
            if ($count <= 0) {
                $data['status'] = 0;
                $data['message'] = '预定失败 暂无房间';
                return $data;
            }
            //有房间
            $this->database_medoo->insert("Reservation_hotel", [
                "User_id" => $this_userid,
                "Hotel_id" => $Hotel_id
            ]);
            $this->database_medoo->update("Hotel", [
                "Hotel_rooms" => $count - 1,
                    ], [
                "Hotel_id" => $Hotel_id
            ]);
            $data['status'] = 1;
            $data['message'] = '预定成功';
            return $data;
        } else {
            //取消预定
            $this->database_medoo->delete("Reservation_hotel", [
                "AND" => [
                    "User_id" => $this_userid,
                    "Hotel_id" => $Hotel_id
                ]
            ]);
            $this->database_medoo->update("Hotel", [
                "Hotel_rooms" => $count + 1,
                    ], [
                "Hotel_id" => $Hotel_id
            ]);
            $data['status'] = 1;
            $data['message'] = '取消预定成功';
            return $data;
        }
    }

}
