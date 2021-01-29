<style lang="less">
@import "./login.less";
</style>

<template>
  <div class="login">
    <div class="login-con">
      <Card icon="log-in" title="欢迎登录" :bordered="false">
        <div class="form-con">
          <login-form @on-success-valid="handleSubmit"></login-form>
        </div>
      </Card>
    </div>
  </div>
</template>

<script>
import LoginForm from "../../components/login-form";
export default {
  components: {
    LoginForm,
  },
  data() {
    return {
      loginForm: {
        username: "",
        password: "",
      },
    };
  },
  methods: {
    handleSubmit({ userName, password }) {
      var that = this;
      var data = {
        username: userName,
        password: password,
      };
      that.$axios
        .post("/api.php?function=Login", data)
        .then(function (response) {
          var result = response.data;
          if (result.status == 1) {
            window.sessionStorage.setItem("token", result.token);
            // window.sessionStorage.setItem("userid", result.userid);
            window.sessionStorage.setItem("username", result.username);
            window.sessionStorage.setItem("roleid", result.roleid);
            window.sessionStorage.setItem("rolename", result.rolename);

            that.$Message.success(result.message);
            that.$router.push({ name: "Flights" }); // 跳转到首页
          } else {
            that.$Message.error(result.message);
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
  },
};
</script>