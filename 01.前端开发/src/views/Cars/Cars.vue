<template>
  <Card :bordered="false" :dis-hover="true">
    <Row>
      <Col span="20">
        <Button
          type="primary"
          @click="add_car_status = true"
          :disabled="!isadmin"
          >添加出租车</Button
        >
      </Col>
      <Col span="4">
        <Input
          search
          enter-button="搜索"
          placeholder="请输入关键词..."
          @on-search="GetCarList"
          v-model="car_search_keywords"
        />
      </Col>
    </Row>
    <div class="page-table">
      <Table :columns="car_column" :data="car_data">
        <template slot-scope="{ row }" slot="id">
          <strong>{{ row.id + 1 }}</strong>
        </template>
        <template slot-scope="{ index }" slot="action">
          <Button
            :type="car_data[index][`is_reserve`] ? 'warning' : 'info'"
            size="small"
            @click="ReserveCar(index)"
            >{{ car_data[index]["is_reserve"] ? "取消" : "预定" }}</Button
          >
          <Button
            type="success"
            size="small"
            @click="editStatus(index)"
            v-if="isadmin"
            >编辑</Button
          >
          <Button
            type="error"
            size="small"
            @click="Deletecar(index)"
            v-if="isadmin"
            >删除</Button
          >
        </template>
      </Table>
      <Card :bordered="false" :dis-hover="true">
        <Page
          v-if="content_total != 0"
          :total="content_total"
          :page-size="page_size"
          @on-change="pageChange"
        />
      </Card>
    </div>
    <Modal v-model="add_car_status" title="添加" @on-ok="AddCar()">
      <Form ref="formCar" :model="formCar" :label-width="80">
        <FormItem label="车牌号">
          <Input v-model="formCar.Car_num" />
        </FormItem>
        <FormItem label="所在城市">
          <Input v-model="formCar.Car_city" />
        </FormItem>
        <FormItem label="价格">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formCar.Car_price"
          ></InputNumber>
        </FormItem>
      </Form>
    </Modal>
    <Modal v-model="edit_car_status" title="编辑" @on-ok="EditCar()">
      <Form ref="formCar" :model="formCar" :label-width="80">
        <FormItem label="车牌号">
          <Input v-model="formCar.Car_num" />
        </FormItem>
        <FormItem label="所在城市">
          <Input v-model="formCar.Car_city" />
        </FormItem>
        <FormItem label="价格">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formCar.Car_price"
          ></InputNumber>
        </FormItem>
      </Form>
    </Modal>
  </Card>
</template>
<script>
export default {
  data() {
    return {
      /**
       * 搜索关键词
       */
      car_search_keywords: "",
      /**
       * 是否为管理员
       */
      isadmin: Number(window.sessionStorage.roleid) == 1,
      /**
       * 出租车数据结构
       */
      formCar: {
        Car_id: "",
        Car_num: "",
        Car_city: "",
        Car_price: 0,
      },
      /**
       * 对话框状态控制
       */
      add_car_status: false, //添加出租车对话框
      edit_car_status: false, //编辑出租车信息对话框
      /**
       * 分页
       */
      current: 1, //当前在第几页
      page_size: 10, //每一页有几条数据
      content_total: 20, //总共有多少条数据
      /**
       * 出租车列表
       */
      car_column: [
        {
          title: "序号",
          key: "id",
          slot: "id",
        },
        {
          title: "车牌号",
          key: "Car_num",
        },
        {
          title: "所在城市",
          key: "Car_city",
        },
        {
          title: "价格",
          key: "Car_price",
        },
        {
          title: "操作",
          slot: "action",
          align: "center",
          width: "190",
        },
      ],
      car_data: [],
    };
  },
  methods: {
    /**
     * 分页改变触发函数
     */
    pageChange(value) {
      var that = this;
      that.current = value; //当前页数
      that.GetCarList();
    },
    /**
     * 预定和取消预定出租车
     */
    ReserveCar(index) {
      var that = this;
      var data = {
        Car_id: that.car_data[index]["Car_id"],
      };
      that.$axios
        .post("/api.php?function=ReserveCar", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetCarList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 编辑出租车信息弹出框
     */
    editStatus(index) {
      var that = this;
      that.edit_car_status = true;
      that.formCar.Car_id = that.car_data[index]["Car_id"];
      that.formCar.Car_num = that.car_data[index]["Car_num"];
      that.formCar.Car_city = that.car_data[index]["Car_city"];
      that.formCar.Car_price = Number(that.car_data[index]["Car_price"]);
    },
    /**
     * 添加出租车
     */
    AddCar() {
      var that = this;
      var data = {
        Car_num: that.formCar.Car_num,
        Car_city: that.formCar.Car_city,
        Car_price: that.formCar.Car_price,
      };
      that.$axios
        .post("/api.php?function=AddCar", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetCarList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 删除出租车信息
     */
    Deletecar(index) {
      var that = this;
      var data = {
        Car_id: that.car_data[index]["Car_id"],
      };
      that.$Modal.confirm({
        title: "警告",
        content: "确定要删除该记录吗？",
        onOk: () => {
          that.$axios
            .post("/api.php?function=Deletecar", data)
            .then(function (response) {
              var result = response.data;
              if (result.status == 1) {
                that.$Message.success(result.message);
                that.GetCarList();
              } else {
                that.$Message.error(result.message);
              }
            })
            .catch(function (error) {
              console.log(error);
            });
        },
        onCancel: () => {},
      });
    },
    /**
     * 编辑出租车信息
     */
    EditCar() {
      var that = this;
      var data = {
        Car_id: that.formCar.Car_id,
        Car_num: that.formCar.Car_num,
        Car_city: that.formCar.Car_city,
        Car_price: that.formCar.Car_price,
      };
      that.$axios
        .post("/api.php?function=EditCar", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetCarList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 获取出租车列表
     */
    GetCarList() {
      var that = this;
      var data = {
        pageSize: that.page_size,
        currentPage: that.current,
        car_search_keywords: that.car_search_keywords,
      };
      that.$axios
        .post("/api.php?function=GetCarList", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            // that.$Message.success(result.message);
            that.car_data = result.data;
            that.content_total = result.total;
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
  },
  created() {},
  mounted() {
    var that = this;
    that.GetCarList();
  },
};
</script>