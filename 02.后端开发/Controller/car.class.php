<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Car {

    private $database_medoo;

    function __construct($database_medoo) {
        $this->database_medoo = $database_medoo;
    }

    //获取出租车列表
    function GetCarList($pageSize, $currentPage, $this_userid, $car_search_keywords) {
        //检查参数
        if (empty($pageSize) || empty($currentPage) || $pageSize == 0 || empty($this_userid) || !isset($car_search_keywords)) {
            $data['status'] = 0;
            $data['message'] = '获取出租车列表失败 参数不完整或错误';
            return $data;
        }
        //处理关键词
        $car_search_keywords = "%$car_search_keywords%";
        //分页
        $begin = $pageSize * ($currentPage - 1);
        //操作
        $list = $this->database_medoo->select("Cars", [
            "Car_id",
            "Car_num",
            "Car_city",
            "Car_price",
            "Car_seats"
                ], [
            "AND" => [
                "OR" => [
                    "Car_num[~]" => $car_search_keywords,
                    "Car_city[~]" => $car_search_keywords,
                    "Car_price[~]" => $car_search_keywords,
                ],
            ],
            "LIMIT" => [$begin, $pageSize]
        ]);
        //查询预定情况
        foreach ($list as $key => $value) {
            $result = $this->database_medoo->select("Reservation_cars", [
                "Reservation_cars_id"
                    ], [
                "User_id" => $this_userid,
                "Car_id" => $value["Car_id"]
            ]);
            $flag = true;
            if (empty($result)) {
                $flag = false;
            }
            $list[$key]["is_reserve"] = $flag;
        }
        //计数
        $total = $this->database_medoo->count('Cars', [
            "AND" => [
                "OR" => [
                    "Car_num[~]" => $car_search_keywords,
                    "Car_city[~]" => $car_search_keywords,
                    "Car_price[~]" => $car_search_keywords,
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
        $data['message'] = '获取出租车列表成功';
        $data['data'] = $list;
        $data['total'] = $total;
        return $data;
    }

    //添加出租车
    function AddCar($Car_num, $Car_city, $Car_price) {
        //检查参数
        if (empty($Car_num) || empty($Car_city) || empty($Car_price)) {
            $data['status'] = 0;
            $data['message'] = '添加失败 参数不完整或错误';
            return $data;
        }
        //检查有无重复
        $result = $this->database_medoo->select("Cars", ["Car_num"], ["Car_num" => $Car_num]);
        if (!empty($result)) {
            $data['status'] = 0;
            $data['message'] = '添加失败 车辆信息已存在';
            return $data;
        }
        //操作
        $this->database_medoo->insert("Cars", [
            "Car_num" => $Car_num,
            "Car_city" => $Car_city,
            "Car_price" => $Car_price,
            "Car_seats" => 1
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '添加出租车信息成功';
        return $data;
    }

    //编辑出租车
    function EditCar($Car_id, $Car_num, $Car_city, $Car_price) {
        //检查参数
        if (empty($Car_id) || empty($Car_num) || empty($Car_city) || empty($Car_price)) {
            $data['status'] = 0;
            $data['message'] = '编辑失败 参数不完整或错误';
            return $data;
        }
        //操作
        $this->database_medoo->update("Cars", [
            "Car_num" => $Car_num,
            "Car_city" => $Car_city,
            "Car_price" => $Car_price,
                ], [
            "Car_id" => $Car_id
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '编辑出租车信息成功';
        return $data;
    }

    //取消和预定出租车
    function ReserveCar($Car_id, $this_userid) {
        //检查参数
        if (empty($Car_id) || empty($this_userid)) {
            $data['status'] = 0;
            $data['message'] = '操作失败 参数不完整或错误';
            return $data;
        }
        //查询余票
        $count = $this->database_medoo->select("Cars", [
            "Car_seats"
                ], [
            "Car_id" => $Car_id
        ]);
        $count = $count[0]["Car_seats"];
        //查询是取消还是预定
        $type = $this->database_medoo->select("Reservation_cars", ["Reservation_cars_id"], [
            "User_id" => $this_userid,
            "Car_id" => $Car_id
        ]);
        //预定
        if (empty($type)) {
            //无余票
            if ($count <= 0) {
                $data['status'] = 0;
                $data['message'] = '预定失败 车辆已被预定';
                return $data;
            }
            //有余票
            $this->database_medoo->insert("Reservation_cars", [
                "User_id" => $this_userid,
                "Car_id" => $Car_id
            ]);
            $this->database_medoo->update("Cars", [
                "Car_seats" => 0,
                    ], [
                "Car_id" => $Car_id
            ]);
            $data['status'] = 1;
            $data['message'] = '预定成功';
            return $data;
        } else {
            //取消预定
            $this->database_medoo->delete("Reservation_cars", [
                "AND" => [
                    "User_id" => $this_userid,
                    "Car_id" => $Car_id
                ]
            ]);
            $this->database_medoo->update("Cars", [
                "Car_seats" => 1,
                    ], [
                "Car_id" => $Car_id
            ]);
            $data['status'] = 1;
            $data['message'] = '取消预定成功';
            return $data;
        }
    }

    //删除出租车
    function Deletecar($Car_id) {
        //检查参数
        if (empty($Car_id)) {
            $data['status'] = 0;
            $data['message'] = '删除出租车信息失败 参数不完整或错误';
            return $data;
        }
        //操作
        $this->database_medoo->delete("Cars", [
            "AND" => [
                "Car_id" => $Car_id
            ]
        ]);
        $this->database_medoo->delete("Reservation_cars", [
            "AND" => [
                "Car_id" => $Car_id
            ]
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '删除出租车信息成功';
        return $data;
    }

}
