<template>
  <Card :bordered="false" :dis-hover="true">
    <Row>
      <Col span="20">
        <Button
          type="primary"
          @click="add_hotel_status = true"
          :disabled="!isadmin"
          >添加宾馆</Button
        >
      </Col>
      <Col span="4">
        <Input
          search
          enter-button="搜索"
          placeholder="请输入关键词..."
          @on-search="GetHotelList"
          v-model="hotel_search_keywords"
        />
      </Col>
    </Row>
    <div class="page-table">
      <Table :columns="hotel_column" :data="hotel_data">
        <template slot-scope="{ row }" slot="id">
          <strong>{{ row.id + 1 }}</strong>
        </template>
        <template slot-scope="{ index }" slot="action">
          <Button
            :type="hotel_data[index][`is_reserve`] ? 'warning' : 'info'"
            size="small"
            @click="ReserveHotel(index)"
            >{{ hotel_data[index]["is_reserve"] ? "取消" : "预定" }}</Button
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
            @click="Deletehotel(index)"
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
    <Modal v-model="add_hotel_status" title="添加" @on-ok="AddHotel()">
      <Form ref="formHotel" :model="formHotel" :label-width="80">
        <FormItem label="宾馆名称">
          <Input v-model="formHotel.Hotel_name" />
        </FormItem>
        <FormItem label="所在城市">
          <Input v-model="formHotel.Hotel_city" />
        </FormItem>
        <FormItem label="房间数">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formHotel.Hotel_rooms"
          ></InputNumber>
        </FormItem>
        <FormItem label="价格">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formHotel.Hotel_price"
          ></InputNumber>
        </FormItem>
      </Form>
    </Modal>
    <Modal v-model="edit_hotel_status" title="编辑" @on-ok="EditHotel()">
      <Form ref="formHotel" :model="formHotel" :label-width="80">
        <FormItem label="宾馆名称">
          <Input v-model="formHotel.Hotel_name" />
        </FormItem>
        <FormItem label="所在城市">
          <Input v-model="formHotel.Hotel_city" />
        </FormItem>
        <FormItem label="房间数">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formHotel.Hotel_rooms"
          ></InputNumber>
        </FormItem>
        <FormItem label="价格">
          <InputNumber
            :max="9999"
            :min="0"
            v-model="formHotel.Hotel_price"
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
      hotel_search_keywords: "",
      /**
       * 是否为管理员
       */
      isadmin: Number(window.sessionStorage.roleid) == 1,
      /**
       * 宾馆数据结构
       */
      formHotel: {
        Hotel_id: "",
        Hotel_name: "",
        Hotel_city: "",
        Hotel_price: 0,
        Hotel_rooms: 0,
      },
      /**
       * 对话框状态控制
       */
      add_hotel_status: false, //添加宾馆对话框
      edit_hotel_status: false, //编辑宾馆信息对话框
      /**
       * 分页
       */
      current: 1, //当前在第几页
      page_size: 10, //每一页有几条数据
      content_total: 20, //总共有多少条数据
      /**
       * 宾馆列表
       */
      hotel_column: [
        {
          title: "序号",
          key: "id",
          slot: "id",
        },
        {
          title: "宾馆名称",
          key: "Hotel_name",
        },
        {
          title: "所在城市",
          key: "Hotel_city",
        },
        {
          title: "剩余房间",
          key: "Hotel_rooms",
        },
        {
          title: "价格",
          key: "Hotel_price",
        },
        {
          title: "操作",
          slot: "action",
          align: "center",
          width: "190",
        },
      ],
      hotel_data: [],
    };
  },
  methods: {
    /**
     * 分页改变触发函数
     */
    pageChange(value) {
      var that = this;
      that.current = value; //当前页数
      that.GetHotelList();
    },
    /**
     * 预定和取消预定宾馆
     */
    ReserveHotel(index) {
      var that = this;
      var data = {
        Hotel_id: that.hotel_data[index]["Hotel_id"],
      };
      that.$axios
        .post("/api.php?function=ReserveHotel", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetHotelList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 编辑宾馆信息弹出框
     */
    editStatus(index) {
      var that = this;
      that.edit_hotel_status = true;
      that.formHotel.Hotel_id = that.hotel_data[index]["Hotel_id"];
      that.formHotel.Hotel_name = that.hotel_data[index]["Hotel_name"];
      that.formHotel.Hotel_city = that.hotel_data[index]["Hotel_city"];
      that.formHotel.Hotel_price = Number(
        that.hotel_data[index]["Hotel_price"]
      );
      that.formHotel.Hotel_rooms = Number(
        that.hotel_data[index]["Hotel_rooms"]
      );
    },
    /**
     * 添加宾馆
     */
    AddHotel() {
      var that = this;
      var data = {
        Hotel_name: that.formHotel.Hotel_name,
        Hotel_city: that.formHotel.Hotel_city,
        Hotel_price: that.formHotel.Hotel_price,
        Hotel_rooms: that.formHotel.Hotel_rooms,
      };
      that.$axios
        .post("/api.php?function=AddHotel", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetHotelList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 删除宾馆信息
     */
    Deletehotel(index) {
      var that = this;
      var data = {
        Hotel_id: that.hotel_data[index]["Hotel_id"],
      };
      that.$Modal.confirm({
        title: "警告",
        content: "确定要删除该记录吗？",
        onOk: () => {
          that.$axios
            .post("/api.php?function=Deletehotel", data)
            .then(function (response) {
              var result = response.data;
              if (result.status == 1) {
                that.$Message.success(result.message);
                that.GetHotelList();
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
     * 编辑宾馆信息
     */
    EditHotel() {
      var that = this;
      var data = {
        Hotel_id: that.formHotel.Hotel_id,
        Hotel_name: that.formHotel.Hotel_name,
        Hotel_city: that.formHotel.Hotel_city,
        Hotel_price: that.formHotel.Hotel_price,
        Hotel_rooms: that.formHotel.Hotel_rooms,
      };
      that.$axios
        .post("/api.php?function=EditHotel", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetHotelList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 获取宾馆列表
     */
    GetHotelList() {
      var that = this;
      var data = {
        pageSize: that.page_size,
        currentPage: that.current,
        hotel_search_keywords: that.hotel_search_keywords,
      };
      that.$axios
        .post("/api.php?function=GetHotelList", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            // that.$Message.success(result.message);
            that.hotel_data = result.data;
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
    that.GetHotelList();
  },
};
</script>