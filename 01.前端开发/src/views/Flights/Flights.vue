<template>
  <Card :bordered="false" :dis-hover="true">
    <Row>
      <Col span="20">
        <Button
          type="primary"
          @click="add_flight_status = true"
          :disabled="!isadmin"
          >添加航班</Button
        >
      </Col>
      <Col span="4">
        <Input
          search
          enter-button="搜索"
          placeholder="请输入关键词..."
          @on-search="GetFlightList"
          v-model="flight_search_keywords"
        />
      </Col>
    </Row>
    <div class="page-table">
      <Table :columns="flight_column" :data="flight_data">
        <template slot-scope="{ row }" slot="id">
          <strong>{{ row.id + 1 }}</strong>
        </template>
        <template slot-scope="{ index }" slot="action">
          <Button
            :type="flight_data[index][`is_reserve`] ? 'warning' : 'info'"
            size="small"
            @click="ReserveFlight(index)"
            >{{ flight_data[index]["is_reserve"] ? "取消" : "预定" }}</Button
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
            @click="Deleteflight(index)"
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
    <Modal v-model="add_flight_status" title="添加" @on-ok="AddFlight()">
      <Form ref="formFlight" :model="formFlight" :label-width="80">
        <FormItem label="航班号">
          <Input v-model="formFlight.Flight_num" />
        </FormItem>
        <FormItem label="出发城市">
          <Input v-model="formFlight.Form_city" />
        </FormItem>
        <FormItem label="到达城市">
          <Input v-model="formFlight.To_city" />
        </FormItem>
        <FormItem label="出发时间">
          <DatePicker
            type="datetime"
            placeholder="选择"
            style="width: 200px"
            v-model="formFlight.Strat_time"
            @on-change="formFlight.Strat_time = $event"
          ></DatePicker>
        </FormItem>
        <FormItem label="到达时间">
          <DatePicker
            type="datetime"
            placeholder="选择"
            style="width: 200px"
            v-model="formFlight.End_time"
            @on-change="formFlight.End_time = $event"
          ></DatePicker>
        </FormItem>
        <FormItem label="座位数">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formFlight.Flight_seats"
          ></InputNumber>
        </FormItem>
        <FormItem label="价格">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formFlight.Flight_price"
          ></InputNumber>
        </FormItem>
      </Form>
    </Modal>
    <Modal v-model="edit_flight_status" title="编辑" @on-ok="EditFlight()">
      <Form ref="formFlight" :model="formFlight" :label-width="80">
        <FormItem label="航班号">
          <Input v-model="formFlight.Flight_num" />
        </FormItem>
        <FormItem label="出发城市">
          <Input v-model="formFlight.Form_city" />
        </FormItem>
        <FormItem label="到达城市">
          <Input v-model="formFlight.To_city" />
        </FormItem>
        <FormItem label="出发时间">
          <DatePicker
            type="datetime"
            placeholder="选择"
            style="width: 200px"
            v-model="formFlight.Strat_time"
            @on-change="formFlight.Strat_time = $event"
          ></DatePicker>
        </FormItem>
        <FormItem label="到达时间">
          <DatePicker
            type="datetime"
            placeholder="选择"
            style="width: 200px"
            v-model="formFlight.End_time"
            @on-change="formFlight.End_time = $event"
          ></DatePicker>
        </FormItem>
        <FormItem label="座位数">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formFlight.Flight_seats"
          ></InputNumber>
        </FormItem>
        <FormItem label="价格">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formFlight.Flight_price"
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
      flight_search_keywords: "",
      /**
       * 是否为管理员
       */
      isadmin: Number(window.sessionStorage.roleid) == 1,
      /**
       * 航班数据结构
       */
      formFlight: {
        Flight_num: "",
        Form_city: "",
        To_city: "",
        Strat_time: "",
        End_time: "",
        Flight_seats: 0,
        Flight_price: 0,
      },
      /**
       * 对话框状态控制
       */
      add_flight_status: false, //添加航班对话框
      edit_flight_status: false, //编辑航班信息对话框
      /**
       * 分页
       */
      current: 1, //当前在第几页
      page_size: 10, //每一页有几条数据
      content_total: 20, //总共有多少条数据
      /**
       * 航班列表
       */
      flight_column: [
        {
          title: "序号",
          key: "id",
          slot: "id",
        },
        {
          title: "航班号",
          key: "Flight_num",
        },
        {
          title: "出发城市",
          key: "Form_city",
        },
        {
          title: "到达城市",
          key: "To_city",
        },
        {
          title: "出发时间",
          key: "Strat_time",
        },
        {
          title: "到达时间",
          key: "End_time",
        },
        {
          title: "剩余座位",
          key: "Flight_seats",
        },
        {
          title: "价格",
          key: "Flight_price",
        },
        {
          title: "操作",
          slot: "action",
          align: "center",
          width: "190",
        },
      ],
      flight_data: [],
      temp: "",
    };
  },
  methods: {
    /**
     * 分页改变触发函数
     */
    pageChange(value) {
      var that = this;
      that.current = value; //当前页数
      that.GetFlightList();
    },
    /**
     * 预定和取消预定航班
     */
    ReserveFlight(index) {
      var that = this;
      var data = {
        Flight_id: that.flight_data[index]["Flight_id"],
      };
      that.$axios
        .post("/api.php?function=ReserveFlight", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetFlightList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 编辑航班信息弹出框
     */
    editStatus(index) {
      var that = this;
      that.edit_flight_status = true;

      that.formFlight.Flight_num = that.flight_data[index]["Flight_num"];
      that.formFlight.Form_city = that.flight_data[index]["Form_city"];
      that.formFlight.To_city = that.flight_data[index]["To_city"];
      that.formFlight.Strat_time = that.flight_data[index]["Strat_time"];
      that.formFlight.End_time = that.flight_data[index]["End_time"];
      that.formFlight.Flight_seats = Number(
        that.flight_data[index]["Flight_seats"]
      );
      that.formFlight.Flight_price = Number(
        that.flight_data[index]["Flight_price"]
      );
    },
    /**
     * 添加航班
     */
    AddFlight() {
      var that = this;
      var data = {
        Flight_num: that.formFlight.Flight_num,
        Form_city: that.formFlight.Form_city,
        To_city: that.formFlight.To_city,
        Strat_time: that.formFlight.Strat_time,
        End_time: that.formFlight.End_time,
        Flight_seats: that.formFlight.Flight_seats,
        Flight_price: that.formFlight.Flight_price,
      };
      that.$axios
        .post("/api.php?function=AddFlight", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetFlightList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 删除航班信息
     */
    Deleteflight(index) {
      var that = this;
      var data = {
        Flight_id: that.flight_data[index]["Flight_id"],
      };
      that.$Modal.confirm({
        title: "警告",
        content: "确定要删除该记录吗？",
        onOk: () => {
          that.$axios
            .post("/api.php?function=Deleteflight", data)
            .then(function (response) {
              var result = response.data;
              if (result.status == 1) {
                that.$Message.success(result.message);
                that.GetFlightList();
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
     * 编辑航班信息
     */
    EditFlight() {
      var that = this;
      var data = {
        Flight_id: that.formFlight.Flight_id,
        Flight_num: that.formFlight.Flight_num,
        Form_city: that.formFlight.Form_city,
        To_city: that.formFlight.To_city,
        Strat_time: that.formFlight.Strat_time,
        End_time: that.formFlight.End_time,
        Flight_seats: that.formFlight.Flight_seats,
        Flight_price: that.formFlight.Flight_price,
      };
      that.$axios
        .post("/api.php?function=EditFlight", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetFlightList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 获取航班列表
     */
    GetFlightList() {
      var that = this;
      var data = {
        pageSize: that.page_size,
        currentPage: that.current,
        flight_search_keywords: that.flight_search_keywords,
      };
      that.$axios
        .post("/api.php?function=GetFlightList", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            // that.$Message.success(result.message);
            that.flight_data = result.data;
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
    that.GetFlightList();
  },
};
</script>