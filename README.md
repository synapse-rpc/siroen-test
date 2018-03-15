## 西纳普斯 - synapse (php Version)
## 测试程序

### 此为系统核心交互组件,包含了事件和RPC系统

### 需要:
> php 7

### 安装依赖:
> composer install

### 运行方式:
> php run.php --host MQ_HOST --user MQ_USER --pass MQ_PASS --sys_name SYSTEM_NAME [--debug] [--server]

因为PHP没有好的多线程支持,所以不能同时运行客户端和服务端
加入--server表示运行服务端

server性能不及其他语言版本,但是如果单运行EventServer或者RpcServer性能会有所好转
client性能没有被影响