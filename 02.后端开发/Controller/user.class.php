<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class User {

    private $database_medoo;

    function __construct($database_medoo) {
        $this->database_medoo = $database_medoo;
    }

    //用户登录
    function Login($username, $password) {
        $result = $this->database_medoo->select('User', [
            "[>]Role" => ["User_roleid" => "Role_id"],
                ], [
            "User.User_id(userid)",
            "User.User_username",
            "User.User_roleid",
            "User.User_password",
            "Role.Role_name"
                ], [
            "User_username" => $username,
            "User_password" => $password
        ]);

        if (empty($result)) {
            $data['status'] = 0;
            $data['message'] = '登录失败 用户不存在或密码错误';
            return $data;
        }
        $token = $this->CreateToken($result[0]['userid']);

        //返回成功信息
        $data['status'] = 1;
        $data['code'] = 200;
        $data['userid'] = $result[0]['userid'];
        $data['username'] = $result[0]['User_username'];
        $data['roleid'] = $result[0]['User_roleid'];
        $data['rolename'] = $result[0]['Role_name'];
        $data['token'] = $token;
        $data['message'] = '登录成功';
        return $data;
    }

    //获取用户列表
    function GetUserList($pageSize, $currentPage, $user_search_keywords) {
        //检查参数
        if (empty($pageSize) || empty($currentPage) || $pageSize == 0) {
            $data['status'] = 0;
            $data['message'] = '获取用户列表失败 参数不完整或错误';
            return $data;
        }
        //关键词处理
        $user_search_keywords = "%$user_search_keywords%";
        //分页处理
        $begin = $pageSize * ($currentPage - 1);
        //所有用户列表
        $list = $this->database_medoo->select('User', [
            "[>]Role" => ["User_roleid" => "Role_id"]
                ], [
            "User.User_id",
            "User.User_username",
            "User.User_password",
            "Role.Role_name",
            "Role.Role_id"
                ], [
            "AND" => [
                "OR" => [
                    "User.User_username[~]" => $user_search_keywords,
                    "User.User_password[~]" => $user_search_keywords,
                    "Role.Role_name[~]" => $user_search_keywords
                ],
            ],
            "LIMIT" => [$begin, $pageSize]
        ]);
        //计数
        $total = $this->database_medoo->count('User', [
            "[>]Role" => ["User_roleid" => "Role_id"]
                ], "*", [
            "OR" => [
                "User.User_username[~]" => $user_search_keywords,
                "User.User_password[~]" => $user_search_keywords,
                "Role.Role_name[~]" => $user_search_keywords
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
        $data['message'] = '获取用户列表成功';
        $data['data'] = $list;
        $data['total'] = $total;
        return $data;
    }

    //添加用户
    function AddUser($Role_id, $User_username, $User_password) {
        //检查参数
        if (empty($Role_id) || empty($User_username) || empty($User_password)) {
            $data['status'] = 0;
            $data['message'] = '添加用户失败 参数不完整或错误';
            return $data;
        }
        //查询是否重复
        $result = $this->database_medoo->select("User", ["User_username"], ["User_username" => $User_username]);
        if (!empty($result)) {
            $data['status'] = 0;
            $data['message'] = '添加失败 用户信息已存在';
            return $data;
        }
        //操作
        $this->database_medoo->insert("User", [
            "User_roleid" => $Role_id,
            "User_username" => $User_username,
            "User_password" => $User_password,
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '添加用户成功';
        return $data;
    }

    //删除用户
    function DeleteUser($User_id) {
        //检查参数
        if (empty($User_id)) {
            $data['status'] = 0;
            $data['message'] = '删除用户失败 参数不完整或错误';
            return $data;
        }
        //操作
        $this->database_medoo->delete("User", [
            "User_id" => $User_id
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '删除用户成功';
        return $data;
    }

    //编辑用户
    function EditUser($User_id, $Role_id, $User_username, $User_password) {
        //检查参数
        if (empty($User_id) || empty($Role_id) || empty($User_username) || empty($User_password)) {
            $data['status'] = 0;
            $data['message'] = '编辑用户失败 参数不完整或错误';
            return $data;
        }
        //操作
        $this->database_medoo->update("User", [
            "User_roleid" => $Role_id,
            "User_username" => $User_username,
            "User_password" => $User_password,
                ], [
            "User_id" => $User_id
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '编辑用户成功';
        return $data;
    }

    //获取用户旅游路线
    function GetUserLine($User_id) {
        //检查参数
        if (empty($User_id)) {
            $data['status'] = 0;
            $data['message'] = '获取失败 参数不完整或错误';
            return $data;
        }
        //操作
        $list = $this->database_medoo->select("Reservation_filghts", [
            "[>]Flights" => ["Flight_id" => "Flight_id"]
                ], [
            "Flights.Form_city",
            "Flights.To_city",
            "Flights.Strat_time",
            "Flights.End_time",
                ], [
            "Reservation_filghts.User_id" => $User_id
        ]);
        $result = array();
        $i = 0;
        foreach ($list as $key => $value) {
            $result[$i]["time"] = $value["Strat_time"];
            $result[$i]['content'] = $value["Form_city"];
            $result[$i + 1]["time"] = $value["End_time"];
            $result[$i + 1]['content'] = $value["To_city"];
            $i += 2;
        }
        //返回
        $data['status'] = 1;
        $data['message'] = '获取用户旅游路线成功';
        $data['data'] = $result;
        return $data;
    }

    //获取当前用户信息
    function GetNowUser($User_id) {
        //检查参数
        if (empty($User_id)) {
            $data['status'] = 0;
            $data['message'] = '获取用户信息失败 参数不完整或错误';
            return $data;
        }
        //操作
        $list = $this->database_medoo->select('User', [
            "User_id",
            "User_username",
                ], [
            "User_id" => $User_id
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '获取用户信息成功';
        $data['data'] = $list[0];
        return $data;
    }

    //更改用户当前信息
    function SetNowUserInfo($User_id, $User_password, $Re_user_password) {
        //检查参数
        if (empty($User_id) || empty($User_password) || empty($Re_user_password)) {
            $data['status'] = 0;
            $data['message'] = '修改用户信息失败 参数不完整或错误';
            return $data;
        }
        if ($User_password != $Re_user_password) {
            $data['status'] = 0;
            $data['message'] = '修改用户信息失败 密码不一致';
            return $data;
        }
        //操作
        $this->database_medoo->update("User", [
            "User_password" => $User_password
                ], [
            "User_id" => $User_id
        ]);
        //返回
        $data['status'] = 1;
        $data['message'] = '修改用户信息成功';
        return $data;
    }

    //获取用户预约列表
    function GetReserveList($pageSize, $currentPage, $User_id) {
        //检查参数
        if (empty($pageSize) || empty($currentPage) || $pageSize == 0 || empty($User_id)) {
            $data['status'] = 0;
            $data['message'] = '获取预约列表失败 参数不完整或错误';
            return $data;
        }
        //分页处理
        $begin = $pageSize * ($currentPage - 1);
        $all = array();
        //航班
        $list = $this->database_medoo->select("Reservation_filghts", [
            "[>]Flights" => ["Flight_id" => "Flight_id"]
                ], [
            "Flights.Flight_id",
            "Flights.Flight_num",
            "Flights.Form_city",
            "Flights.To_city",
            "Flights.Strat_time",
            "Flights.End_time",
            "Flights.Flight_price",
                ], [
            "Reservation_filghts.User_id" => $User_id
        ]);
        foreach ($list as $key => $value) {
            $temp = array(
                "reserve_id" => $value["Flight_id"],
                "reserve_type_id" => "1",
                "reserve_type_name" => "航班",
                "reserve_num" => $value["Flight_num"],
                "reserve_city" => $value["Form_city"] . " -> " . $value["To_city"],
                "reserve_time" => $value["Strat_time"] . " -> " . $value["End_time"],
                "reserve_price" => $value["Flight_price"],
            );
            array_push($all, $temp);
        }
        //宾馆
        $list = $this->database_medoo->select("Reservation_hotel", [
            "[>]Hotel" => ["Hotel_id" => "Hotel_id"]
                ], [
            "Hotel.Hotel_id",
            "Hotel.Hotel_name",
            "Hotel.Hotel_price",
            "Hotel.Hotel_city",
                ], [
            "Reservation_hotel.User_id" => $User_id
        ]);
        foreach ($list as $key => $value) {
            $temp = array(
                "reserve_id" => $value["Hotel_id"],
                "reserve_type_id" => "2",
                "reserve_type_name" => "宾馆",
                "reserve_num" => $value["Hotel_name"],
                "reserve_city" => $value["Hotel_city"],
                "reserve_time" => "-",
                "reserve_price" => $value["Hotel_price"],
            );
            array_push($all, $temp);
        }
        //出租车
        $list = $this->database_medoo->select("Reservation_cars", [
            "[>]Cars" => ["Car_id" => "Car_id"]
                ], [
            "Cars.Car_id",
            "Cars.Car_num",
            "Cars.Car_price",
            "Cars.Car_city",
                ], [
            "Reservation_cars.User_id" => $User_id
        ]);
        foreach ($list as $key => $value) {
            $temp = array(
                "reserve_id" => $value["Car_id"],
                "reserve_type_id" => "3",
                "reserve_type_name" => "出租车",
                "reserve_num" => $value["Car_num"],
                "reserve_city" => $value["Car_city"],
                "reserve_time" => "-",
                "reserve_price" => $value["Car_price"],
            );
            array_push($all, $temp);
        }
        //返回
        $data['status'] = 1;
        $data['message'] = '获取预约列表成功';
        $data['data'] = array_slice($all, $begin, $pageSize);
        $data['total'] = sizeof($all);
        return $data;
    }

    function ReserveCancel($reserve_type_id, $reserve_id) {
        //检查参数
        if (empty($reserve_type_id) || empty($reserve_id)) {
            $data['status'] = 0;
            $data['message'] = '取消失败 参数不完整或错误';
            return $data;
        }
        switch ($reserve_type_id) {
            case "1": {
                    $this->database_medoo->delete("Reservation_filghts", ["Reservation_filghts_id" => $reserve_id]);
                }
                break;
            case "2": {
                    $this->database_medoo->delete("Reservation_hotel", ["Reservation_hotel_id" => $reserve_id]);
                }
                break;
            case "3": {
                    $this->database_medoo->delete("Reservation_cars", ["Reservation_cars_id" => $reserve_id]);
                }
                break;
        }
        //返回
        $data['status'] = 1;
        $data['message'] = "取消成功";
        return $data;
    }

    //生成token
    public function CreateToken($userid) {
        $time = time();
        $end_time = time() + 86400;
        $info = $userid . '.' . $time . '.' . $end_time; //设置token过期时间为一天
        //根据以上信息信息生成签名（密钥为 siasqr)
        $signature = hash_hmac('md5', $info, SIGNATURE);
        //最后将这两部分拼接起来，得到最终的Token字符串
        return $token = $info . '.' . $signature;
    }

    //检查token
    public function CheckToken($token) {
        if (!isset($token) || empty($token)) {
            $data['code'] = '400';
            $data['message'] = '非法请求';
            return $data;
        }
        //对比token
        $explode = explode('.', $token); //以.分割token为数组
        if (!empty($explode[0]) && !empty($explode[1]) && !empty($explode[2]) && !empty($explode[3])) {
            $info = $explode[0] . '.' . $explode[1] . '.' . $explode[2]; //信息部分
            $true_signature = hash_hmac('md5', $info, SIGNATURE); //正确的签名
            if (time() > $explode[2]) {
                $data['code'] = '401';
                $data['message'] = 'Token已过期,请重新登录';
                return $data;
            }
            if ($true_signature == $explode[3]) {
                $data['code'] = '200';
                $data['message'] = 'Token合法';
                return $data;
            } else {
                $data['code'] = '400';
                $data['message'] = 'Token不合法';
                return $data;
            }
        } else {
            $data['code'] = '400';
            $data['message'] = 'Token不合法';
            return $data;
        }
    }

    //根据token获取userid
    public function GetUserInfoByToken($token) {
        $explode = explode('.', $token);
        $result = $this->database_medoo->select("user", ["username"], ["id" => $explode[0]]);
        $data = array(
            "userid" => $explode[0],
            "username" => $result[0]["username"]
        );
        return $data;
    }

}
