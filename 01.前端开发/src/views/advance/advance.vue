<style scoped>
.time {
  font-size: 14px;
  font-weight: bold;
}
.content {
  padding-left: 5px;
}
</style>
<template>
  <Card :bordered="false" :dis-hover="true">
    <Tabs value="tab2" type="card">
      <TabPane label="用户管理" name="tab1" v-if="isadmin">
        <Row>
          <Col span="20">
            <Button type="primary" @click="add_user_status = true"
              >添加用户</Button
            >
          </Col>
          <Col span="4">
            <Input
              search
              enter-button="搜索"
              placeholder="请输入关键词..."
              @on-search="GetUserList"
              v-model="user_search_keywords"
            />
          </Col>
        </Row>
        <div class="page-table">
          <Table :columns="user_column" :data="user_data">
            <template slot-scope="{ row }" slot="id">
              <strong>{{ row.id + 1 }}</strong>
            </template>
            <template slot-scope="{ index }" slot="User_line">
              <Button type="success" size="small" @click="lineStatus(index)"
                >查看</Button
              >
            </template>
            <template slot-scope="{ index }" slot="action">
              <Button type="success" size="small" @click="editStatus(index)"
                >编辑</Button
              >
              <Button type="error" size="small" @click="DeleteUser(index)"
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
      </TabPane>
      <TabPane label="个人预约" name="tab2">
        <div class="page-table">
          <Table :columns="reserve_column" :data="reserve_data">
            <template slot-scope="{ index }" slot="action">
              <Button type="warning" size="small" @click="ReserveCancel(index)"
                >取消</Button
              >
            </template>
          </Table>
          <Card :bordered="false" :dis-hover="true">
            <Page
              v-if="content_total_r != 0"
              :total="content_total_r"
              :page-size="page_size_r"
              @on-change="pageChangeR"
            />
          </Card>
        </div>
      </TabPane>
      <TabPane label="密码修改" name="tab3">
        <Card :bordered="false" :dis-hover="true" style="width: 380px">
          <Form ref="nowUser" :model="nowUser" :label-width="90">
            <FormItem label="用户名">
              <Input v-model="nowUser.User_username" disabled />
            </FormItem>
            <FormItem label="密码">
              <Input v-model="nowUser.User_password" type="password" password />
            </FormItem>
            <FormItem label="重复密码">
              <Input
                v-model="nowUser.Re_user_password"
                type="password"
                password
              />
            </FormItem>
            <FormItem>
              <Button type="primary" @click="SetNowUserInfo()">保存</Button>
            </FormItem>
          </Form>
        </Card>
      </TabPane>
    </Tabs>
    <Modal v-model="add_user_status" title="添加" @on-ok="AddUser()">
      <Form ref="formUser" :model="formUser" :label-width="80">
        <FormItem label="用户名">
          <Input v-model="formUser.User_username" />
        </FormItem>
        <FormItem label="密码">
          <Input v-model="formUser.User_password" />
        </FormItem>
        <FormItem label="角色">
          <Select v-model="formUser.User_roleid" style="width: 200px">
            <Option
              v-for="item in roleList"
              :value="item.value"
              :key="item.value"
              >{{ item.label }}</Option
            >
          </Select>
        </FormItem>
      </Form>
    </Modal>
    <Modal v-model="edit_user_status" title="编辑" @on-ok="EditUser()">
      <Form ref="formUser" :model="formUser" :label-width="80">
        <FormItem label="用户名">
          <Input v-model="formUser.User_username" />
        </FormItem>
        <FormItem label="密码">
          <Input v-model="formUser.User_password" />
        </FormItem>
        <FormItem label="角色">
          <Select v-model="formUser.User_roleid" style="width: 200px">
            <Option
              v-for="item in roleList"
              :value="item.value"
              :key="item.value"
              >{{ item.label }}</Option
            >
          </Select>
        </FormItem>
      </Form>
    </Modal>
    <Modal v-model="view_line_status" title="旅行路线">
      <Scroll>
        <Timeline>
          <TimelineItem v-for="(item, index) in lineList" :key="index">
            <p class="time">{{ item.time }}</p>
            <p class="content">{{ item.content }}</p>
          </TimelineItem>
        </Timeline>
      </Scroll>
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
      user_search_keywords: "",
      lineList: [
        {
          time: "2000年",
          content: "出生",
        },
        {
          time: "2001年",
          content: "长大",
        },
      ],
      /**
       * 当前用户数据结构
       */
      nowUser: {
        User_id: "",
        User_username: "",
        User_password: "",
        Re_user_password: "",
      },
      /**
       * 是否为管理员
       */
      isadmin: Number(window.sessionStorage.roleid) == 1,
      /**
       * 用户数据结构
       */
      formUser: {
        User_id: "",
        User_username: "",
        User_password: "",
        User_roleid: "2",
      },
      /**
       * 角色下拉列表
       */
      roleList: [
        {
          value: "1",
          label: "admin",
        },
        {
          value: "2",
          label: "user",
        },
      ],
      /**
       * 对话框状态控制
       */
      add_user_status: false, //添加用户对话框
      edit_user_status: false, //编辑用户信息对话框
      view_line_status: false, //查看旅行路线对话框
      /**
       * 分页
       */
      current: 1, //当前在第几页
      page_size: 10, //每一页有几条数据
      content_total: 20, //总共有多少条数据
      /**
       * 预约页面分页
       */
      current_r: 1, //当前在第几页
      page_size_r: 10, //每一页有几条数据
      content_total_r: 20, //总共有多少条数据
      /**
       * 用户列表
       */
      user_column: [
        {
          title: "序号",
          key: "id",
          slot: "id",
        },
        {
          title: "用户名",
          key: "User_username",
        },
        {
          title: "密码",
          key: "User_password",
        },
        {
          title: "角色",
          key: "Role_name",
        },
        {
          title: "旅行路线",
          slot: "User_line",
        },
        {
          title: "操作",
          slot: "action",
          align: "center",
          width: "190",
        },
      ],
      user_data: [],
      /**
       * 个人预约列表
       */
      reserve_column: [
        // {
        //   title: "序号",
        //   key: "id",
        //   slot: "id",
        // },
        {
          title: "预约类型",
          key: "reserve_type_name",
        },
        {
          title: "航班号/宾馆/车牌号",
          key: "reserve_num",
        },
        {
          title: "城市信息",
          key: "reserve_city",
        },
        {
          title: "时间信息",
          key: "reserve_time",
          width: "340",
        },
        {
          title: "价格信息",
          key: "reserve_price",
        },
        {
          title: "操作",
          slot: "action",
          align: "center",
          width: "190",
        },
      ],
      reserve_data: [],
    };
  },
  methods: {
    /**
     * 分页改变触发函数
     */
    pageChange(value) {
      var that = this;
      that.current = value; //当前页数
      that.GetUserList();
    },
    /**
     * 取消预约
     */
    ReserveCancel(index) {
      var that = this;
      var data = {
        reserve_type_id: that.reserve_data[index]["reserve_type_id"],
        reserve_id: that.reserve_data[index]["reserve_id"],
      };
      that.$Modal.confirm({
        title: "警告",
        content: "确定要取消预约吗？",
        onOk: () => {
          that.$axios
            .post("/api.php?function=ReserveCancel", data)
            .then(function (response) {
              var result = response.data;
              if (result.status == 1) {
                that.$Message.success(result.message);
                that.GetReserveList();
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
     * 分页改变触发函数
     */
    pageChangeR(value) {
      var that = this;
      that.current_r = value; //当前页数
      that.GetReserveList();
    },
    /**
     * 设置当前用户信息
     */
    SetNowUserInfo() {
      var that = this;
      var data = {
        User_password: that.nowUser.User_password,
        Re_user_password: that.nowUser.Re_user_password,
      };
      that.$axios
        .post("/api.php?function=SetNowUserInfo", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetNowUser();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 获取当前用户信息
     */
    GetNowUser() {
      var that = this;
      var data = {};
      that.$axios
        .post("/api.php?function=GetNowUser", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            // that.$Message.success(result.message);
            that.nowUser = result.data;
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 编辑用户信息弹出框
     */
    editStatus(index) {
      var that = this;
      that.edit_user_status = true;
      that.formUser.User_id = that.user_data[index]["User_id"];
      that.formUser.User_username = that.user_data[index]["User_username"];
      that.formUser.User_password = that.user_data[index]["User_password"];
      that.formUser.User_roleid = that.user_data[index]["Role_id"];
    },
    /**
     * 查看旅行路线弹出框
     */
    lineStatus(index) {
      var that = this;
      that.view_line_status = true;
      that.GetUserLine(that.user_data[index]["User_id"]);
    },
    /**
     * 获取用户旅游路线
     */
    GetUserLine(User_id) {
      var that = this;
      var data = {
        User_id: User_id,
      };
      that.$axios
        .post("/api.php?function=GetUserLine", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            // that.$Message.success(result.message);
            that.lineList = result.data;
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 添加用户
     */
    AddUser() {
      var that = this;
      var data = {
        User_username: that.formUser.User_username,
        User_password: that.formUser.User_password,
        User_roleid: that.formUser.User_roleid,
      };
      that.$axios
        .post("/api.php?function=AddUser", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetUserList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 删除用户信息
     */
    DeleteUser(index) {
      var that = this;
      var data = {
        User_id: that.user_data[index]["User_id"],
      };
      that.$Modal.confirm({
        title: "警告",
        content: "确定要删除该记录吗？",
        onOk: () => {
          that.$axios
            .post("/api.php?function=DeleteUser", data)
            .then(function (response) {
              var result = response.data;
              if (result.status == 1) {
                that.$Message.success(result.message);
                that.GetUserList();
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
     * 编辑用户信息
     */
    EditUser() {
      var that = this;
      var data = {
        User_id: that.formUser.User_id,
        User_username: that.formUser.User_username,
        User_password: that.formUser.User_password,
        User_roleid: that.formUser.User_roleid,
      };
      that.$axios
        .post("/api.php?function=EditUser", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            that.$Message.success(result.message);
            that.GetUserList();
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 获取用户列表
     */
    GetUserList() {
      var that = this;
      var data = {
        pageSize: that.page_size,
        currentPage: that.current,
        user_search_keywords: that.user_search_keywords,
      };
      that.$axios
        .post("/api.php?function=GetUserList", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            // that.$Message.success(result.message);
            that.user_data = result.data;
            that.content_total = result.total;
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /**
     * 获取预约列表
     */
    GetReserveList() {
      var that = this;
      var data = {
        pageSize: that.page_size_r,
        currentPage: that.current_r,
      };
      that.$axios
        .post("/api.php?function=GetReserveList", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            // that.$Message.success(result.message);
            that.reserve_data = result.data;
            that.content_total_r = result.total;
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
    that.GetUserList();
    that.GetNowUser();
    that.GetReserveList();
  },
};
</script>