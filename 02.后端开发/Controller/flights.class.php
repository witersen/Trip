<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Flights {

    private $database_medoo;

    function __construct($database_medoo) {
        $this->database_medoo = $database_medoo;
    }

    //获取航班列表
    function GetFlightList($pageSize, $currentPage, $this_userid, $flight_search_keywords) {
        //检查参数
        if (empty($pageSize) || empty($currentPage) || $pageSize == 0 || empty($this_userid) || !isset($flight_search_keywords)) {
            $data['status'] = 0;
            $data['message'] = '获取航班列表失败 参数不完整或错误';
            return $data;
        }
        //处理关键词
        $flight_search_keywords = "%$flight_search_keywords%";
        //分页
        $begin = $pageSize * ($currentPage - 1);
        //操作
        $list = $this->database_medoo->select("Flights", [
            "Flight_id",
            "Flight_num",
            "Form_city",
            "To_city",
            "Strat_time",
            "End_time",
            "Flight_seats",
            "Flight_price",
                ], [
            "AND" => [
                "OR" => [
                    "Flights.Flight_num[~]" => $flight_search_keywords,
                    "Flights.Form_city[~]" => $flight_search_keywords,
                    "Flights.To_city[~]" => $flight_search_keywords,
                    "Flights.Strat_time[~]" => $flight_search_keywords,
                    "Flights.End_time[~]" => $flight_search_keywords,
                    "Flights.Flight_seats[~]" => $flight_search_keywords,
                    "Flights.Flight_price[~]" => $flight_search_keywords,
                ],
            ],
            "LIMIT" => [$begin, $pageSize]
        ]);
        //查询预定情况
        foreach ($list as $key => $value) {
            $result = $this->database_medoo->select("Reservation_filghts", [
                "Reservation_filghts_id"
                    ], [
                "User_id" => $this_userid,
                "Flight_id" => $value["Flight_id"]
            ]);
            $flag = true;
            if (empty($result)) {
                $flag = false;
            }
            $list[$key]["is_reserve"] = $flag;
        }
        //计数
        $total = $this->database_medoo->count('Flights', [
            "AND" => [
                "OR" => [
                    "Flight_num[~]" => $flight_search_keywords,
                    "Flight_price[~]" => $flight_search_keywords,
                    "Form_city[~]" => $flight_search_keywords,
                    "To_city[~]" => $flight_search_keywords,
                    "Strat_time[~]" => $flight_search_keywords,
                    "End_time[~]" => $flight_search_keywords,
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
        $data['message'] = '获取航班列表成功';
        $data['data'] = $list;
        $data['total'] = $total;
        return $data;
    }

    //添加航班
    function AddFlight($Flight_num, $Form_city, $To_city, $Strat_time, $End_time, $Flight_seats, $Flight_price) {
        //检查参数
        if (empty($Flight_num) || empty($Form_city) || empty($To_city) || empty($Strat_time) || empty($End_time) || empty($Flight_seats) || empty($Flight_price)) {
            $data['status'] = 0;
            $data['message'] = '添加失败 参数不完整或错误';
            return $data;
        }
        //检查有无重复
        $result = $this->database_medoo->select("Flights", ["Flight_num"], ["Flight_num" => $Flight_num]);
        if (!empty($result)) {
            $data['status'] = 0;
            $data['message'] = '添加失败 航班号已存在';
            return $data;
        }
        //操作
        $this->database_medoo->insert("Flights", [
            "Flight_num" => $Flight_num,
            "Form_city" => $Form_city,
            "To_city" => $To_city,
            "Strat_time" => $Strat_time,
            "End_time" => $End_time,
            "Flight_seats" => $Flight_seats,
            "Flight_price" => $Flight_price,
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '添加航班信息成功';
        return $data;
    }

    //编辑航班
    function EditFlight($Flight_id, $Flight_num, $Form_city, $To_city, $Strat_time, $End_time, $Flight_seats, $Flight_price) {
        //检查参数
        if (empty($Flight_id) || empty($Flight_num) || empty($Form_city) || empty($To_city) || empty($Strat_time) || empty($End_time) || empty($Flight_seats) || empty($Flight_price)) {
            $data['status'] = 0;
            $data['message'] = '编辑失败 参数不完整或错误';
            return $data;
        }
        //操作
        $this->database_medoo->update("Flights", [
            "Flight_num" => $Flight_num,
            "Form_city" => $Form_city,
            "To_city" => $To_city,
            "Strat_time" => $Strat_time,
            "End_time" => $End_time,
            "Flight_seats" => $Flight_seats,
            "Flight_price" => $Flight_price,
                ], [
            "Flight_id" => $Flight_id
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '编辑航班信息成功';
        return $data;
    }

    //删除航班
    function Deleteflight($Flight_id) {
        //检查参数
        if (empty($Flight_id)) {
            $data['status'] = 0;
            $data['message'] = '删除航班信息失败 参数不完整或错误';
            return $data;
        }
        //操作
        $this->database_medoo->delete("Flights", [
            "AND" => [
                "Flight_id" => $Flight_id
            ]
        ]);
        $this->database_medoo->delete("Reservation_filghts", [
            "AND" => [
                "Flight_id" => $Flight_id
            ]
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '删除航班信息成功';
        return $data;
    }

    //取消和预定航班
    function ReserveFlight($Flight_id, $this_userid) {
        //检查参数
        if (empty($Flight_id) || empty($this_userid)) {
            $data['status'] = 0;
            $data['message'] = '操作失败 参数不完整或错误';
            return $data;
        }
        //查询余票
        $count = $this->database_medoo->select("Flights", [
            "Flight_seats"
                ], [
            "Flight_id" => $Flight_id
        ]);
        $count = $count[0]["Flight_seats"];
        //查询是取消还是预定
        $type = $this->database_medoo->select("Reservation_filghts", ["Reservation_filghts_id"], [
            "User_id" => $this_userid,
            "Flight_id" => $Flight_id
        ]);
        //预定
        if (empty($type)) {
            //无余票
            if ($count <= 0) {
                $data['status'] = 0;
                $data['message'] = '预定失败 暂无余票';
                return $data;
            }
            //有余票
            $this->database_medoo->insert("Reservation_filghts", [
                "User_id" => $this_userid,
                "Flight_id" => $Flight_id
            ]);
            $this->database_medoo->update("Flights", [
                "Flight_seats" => $count - 1,
                    ], [
                "Flight_id" => $Flight_id
            ]);
            $data['status'] = 1;
            $data['message'] = '预定成功';
            return $data;
        } else {
            //取消预定
            $this->database_medoo->delete("Reservation_filghts", [
                "AND" => [
                    "User_id" => $this_userid,
                    "Flight_id" => $Flight_id
                ]
            ]);
            $this->database_medoo->update("Flights", [
                "Flight_seats" => $count + 1,
                    ], [
                "Flight_id" => $Flight_id
            ]);
            $data['status'] = 1;
            $data['message'] = '取消预定成功';
            return $data;
        }
    }

}
