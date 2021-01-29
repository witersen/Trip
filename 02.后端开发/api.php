<?php

define('BASE_PATH', __DIR__);

date_default_timezone_set('PRC'); //获取时间与实际时间差8小时的解决办法

require_once BASE_PATH . '/Controller/car.class.php';
require_once BASE_PATH . '/Controller/flights.class.php';
require_once BASE_PATH . '/Controller/hotel.class.php';
require_once BASE_PATH . '/Controller/user.class.php';
require_once BASE_PATH . '/Data/connection.php';

$Car = new Car($database_medoo);
$Flights = new Flights($database_medoo);
$Hotel = new Hotel($database_medoo);
$User = new User($database_medoo);

$requestPayload = file_get_contents("php://input");
$requestPayload = !empty($requestPayload) ? json_decode($requestPayload, true) : array();
$function = $_GET['function']; //程序正常请求使用

/*
 * 除登录接口外其余接口都需校验token
 */
$thisToken = $_SERVER['HTTP_TOKEN'];
if ($function != 'Login') {
    $data = $User->CheckToken($thisToken);
    if ($data['code'] != '200') {
        echo json_encode(array(
            'status' => '0',
            'code' => $data['code'],
            'message' => $data['message']
        ));
        return;
    }
}

/*
 * 根据token获取前端用户的userid，防止前端篡改用户id
 */
$this_userid = $User->GetUserInfoByToken($thisToken)["userid"];
$this_username = $User->GetUserInfoByToken($thisToken)["username"];

/*
 * switch case
 */
switch ($function) {
    /*
     * flight
     */
    case 'GetFlightList': {
            $pageSize = $requestPayload['pageSize'];
            $currentPage = $requestPayload['currentPage'];
            $flight_search_keywords = trim($requestPayload['flight_search_keywords']);
            echo json_encode($Flights->GetFlightList($pageSize, $currentPage, $this_userid, $flight_search_keywords));
        };
        break;
    case 'AddFlight': {
            $Flight_num = trim($requestPayload['Flight_num']);
            $Form_city = trim($requestPayload['Form_city']);
            $To_city = trim($requestPayload['To_city']);
            $Strat_time = trim($requestPayload['Strat_time']);
            $End_time = trim($requestPayload['End_time']);
            $Flight_seats = $requestPayload['Flight_seats'];
            $Flight_price = $requestPayload['Flight_price'];
            echo json_encode($Flights->AddFlight($Flight_num, $Form_city, $To_city, $Strat_time, $End_time, $Flight_seats, $Flight_price));
        };
        break;
    case 'Deleteflight': {
            $Flight_id = $requestPayload['Flight_id'];
            echo json_encode($Flights->Deleteflight($Flight_id));
        };
        break;
    case 'EditFlight': {
            $Flight_id = $requestPayload['Flight_id'];
            $Flight_num = trim($requestPayload['Flight_num']);
            $Form_city = trim($requestPayload['Form_city']);
            $To_city = trim($requestPayload['To_city']);
            $Strat_time = trim($requestPayload['Strat_time']);
            $End_time = trim($requestPayload['End_time']);
            $Flight_seats = $requestPayload['Flight_seats'];
            $Flight_price = $requestPayload['Flight_price'];
            echo json_encode($Flights->EditFlight($Flight_id, $Flight_num, $Form_city, $To_city, $Strat_time, $End_time, $Flight_seats, $Flight_price));
        };
        break;
    case 'ReserveFlight': {
            $Flight_id = $requestPayload['Flight_id'];
            echo json_encode($Flights->ReserveFlight($Flight_id, $this_userid));
        };
        break;
    /*
     * hotel
     */
    case 'GetHotelList': {
            $pageSize = $requestPayload['pageSize'];
            $currentPage = $requestPayload['currentPage'];
            $hotel_search_keywords = trim($requestPayload['hotel_search_keywords']);
            echo json_encode($Hotel->GetHotelList($pageSize, $currentPage, $this_userid, $hotel_search_keywords));
        };
        break;
    case 'AddHotel': {
            $Hotel_name = trim($requestPayload['Hotel_name']);
            $Hotel_city = trim($requestPayload['Hotel_city']);
            $Hotel_price = $requestPayload['Hotel_price'];
            $Hotel_rooms = $requestPayload['Hotel_rooms'];
            echo json_encode($Hotel->AddHotel($Hotel_name, $Hotel_city, $Hotel_price, $Hotel_rooms));
        };
        break;
    case 'EditHotel': {
            $Hotel_id = $requestPayload['Hotel_id'];
            $Hotel_name = trim($requestPayload['Hotel_name']);
            $Hotel_city = trim($requestPayload['Hotel_city']);
            $Hotel_price = $requestPayload['Hotel_price'];
            $Hotel_rooms = $requestPayload['Hotel_rooms'];
            echo json_encode($Hotel->EditHotel($Hotel_id, $Hotel_name, $Hotel_city, $Hotel_price, $Hotel_rooms));
        };
        break;
    case 'Deletehotel': {
            $Hotel_id = $requestPayload['Hotel_id'];
            echo json_encode($Hotel->Deletehotel($Hotel_id));
        };
        break;
    case 'ReserveHotel': {
            $Hotel_id = $requestPayload['Hotel_id'];
            echo json_encode($Hotel->ReserveHotel($Hotel_id, $this_userid));
        };
        break;
    /*
     * Car
     */
    case 'AddCar': {
            $Car_num = trim($requestPayload['Car_num']);
            $Car_city = trim($requestPayload['Car_city']);
            $Car_price = $requestPayload['Car_price'];
            echo json_encode($Car->AddCar($Car_num, $Car_city, $Car_price));
        };
        break;
    case 'GetCarList': {
            $pageSize = $requestPayload['pageSize'];
            $currentPage = $requestPayload['currentPage'];
            $car_search_keywords = trim($requestPayload['car_search_keywords']);
            echo json_encode($Car->GetCarList($pageSize, $currentPage, $this_userid, $car_search_keywords));
        };
        break;
    case 'EditCar': {
            $Car_id = $requestPayload['Car_id'];
            $Car_num = trim($requestPayload['Car_num']);
            $Car_city = trim($requestPayload['Car_city']);
            $Car_price = $requestPayload['Car_price'];
            echo json_encode($Car->EditCar($Car_id, $Car_num, $Car_city, $Car_price));
        };
        break;
    case 'ReserveCar': {
            $Car_id = $requestPayload['Car_id'];
            echo json_encode($Car->ReserveCar($Car_id, $this_userid));
        };
        break;
    case 'Deletecar': {
            $Car_id = $requestPayload['Car_id'];
            echo json_encode($Car->Deletecar($Car_id));
        };
        break;
    /*
     * User
     */
    case 'Login': {
            $username = trim($requestPayload['username']);
            $password = trim($requestPayload['password']);
            echo json_encode($User->Login($username, $password));
        };
        break;
    case 'GetUserList': {
            $pageSize = $requestPayload['pageSize'];
            $currentPage = $requestPayload['currentPage'];
            $user_search_keywords = trim($requestPayload['user_search_keywords']);
            echo json_encode($User->GetUserList($pageSize, $currentPage, $user_search_keywords));
        };
        break;
    case 'AddUser': {
            $Role_id = $requestPayload['User_roleid'];
            $User_username = $requestPayload['User_username'];
            $User_password = $requestPayload['User_password'];
            echo json_encode($User->AddUser($Role_id, $User_username, $User_password));
        };
        break;
    case 'DeleteUser': {
            $User_id = $requestPayload['User_id'];
            echo json_encode($User->DeleteUser($User_id));
        };
        break;
    case 'EditUser': {
            $User_id = $requestPayload['User_id'];
            $Role_id = $requestPayload['User_roleid'];
            $User_username = $requestPayload['User_username'];
            $User_password = $requestPayload['User_password'];
            echo json_encode($User->EditUser($User_id, $Role_id, $User_username, $User_password));
        };
        break;
    case 'GetUserLine': {
            $User_id = $requestPayload['User_id'];
            echo json_encode($User->GetUserLine($User_id));
        };
        break;
    case 'GetNowUser': {
            echo json_encode($User->GetNowUser($this_userid));
        };
        break;
    case 'SetNowUserInfo': {
            $User_password = $requestPayload['User_password'];
            $Re_user_password = $requestPayload['Re_user_password'];
            echo json_encode($User->SetNowUserInfo($this_userid, $User_password, $Re_user_password));
        };
        break;
    case 'GetReserveList': {
            $pageSize = $requestPayload['pageSize'];
            $currentPage = $requestPayload['currentPage'];
            echo json_encode($User->GetReserveList($pageSize, $currentPage, $this_userid));
        };
        break;
    case 'ReserveCancel': {
            $reserve_type_id = $requestPayload["reserve_type_id"];
            $reserve_id = $requestPayload["reserve_id"];
            switch ($reserve_type_id) {
                case "1": {
                        echo json_encode($Flights->ReserveFlight($reserve_id, $this_userid));
                    }
                    break;
                case "2": {
                        echo json_encode($Hotel->ReserveHotel($reserve_id, $this_userid));
                    }
                    break;
                case "3": {
                        echo json_encode($Car->ReserveCar($reserve_id, $this_userid));
                    }
                    break;
            }
        };
        break;
}