使用 sphinx 离线语音库实现 DKbot 语音遥控运动。

<!--more-->

# 第 1 章 系统环境

* Ubuntu Mate 16.04
* ROS Kinetic

# 第 2 章 安装

## 2.1 库和组件

第（2）到（4）步中需下载对应版本的 deb 包，比如树莓派需下载 armhf 版本的。

![dkbot3_01](http://www.dengke.xyz/usr/uploads/2019/11/2019828155.png)

（1）依赖：

```shell
sudo apt-get install ros-kinetic-audio-common libasound2 gstreamer0.10-* python-gst0.10
```

（2）libsphinxbase：https://packages.debian.org/jessie/libsphinxbase1

```shell
sudo dpkg -i libsphinxbase1_0.8-6_armhf.deb
```

（3）libpocketsphinx：https://packages.debian.org/jessie/libpocketsphinx1

```shell
sudo dpkg -i libpocketsphinx1_0.8-5_ armhf.deb
```

（4）gstreamer：https://packages.debian.org/jessie/gstreamer0.10-pocketsphinx

```shell
sudo dpkg -i gstreamer0.10-pocketsphinx_0.8-5_ armhf.deb
```

## 2.2 功能包

pocketsphinx 功能包，用于开发机器人语音识别。这个包只支持到 Indigo 版本，且暂未找到用于 Kinetic 的可替代包，因此需进行部分修改，见下文。

```shell
cd ~/catkin_ws/src
git clone https://github.com/mikeferguson/pocketsphinx
```

下载完成后，在 pocketsphinx 包中创建目录 model/hmm 和 model/lm，二者分别用于存放语音模型和离线词库。

![dkbot3_02](http://www.dengke.xyz/usr/uploads/2019/11/1588730139.png)

## 2.3 语音模型

官方语音模型，下载 cmusphinx-en-us-8khz-5.2.tar.gz，该模型采用 hmm(隐马尔可夫模型)建模。下载完成后，将压缩包解压至树莓派 pocketsphinx/model/hmm 目录下。

https://sourceforge.net/projects/cmusphinx/files/Acoustic%20and%20Language%20Models/US%20English/

![dkbot3_03](http://www.dengke.xyz/usr/uploads/2019/11/1355081258.png)

## 2.4 离线词库

（1）创建.txt 文档，按如下格式编写文本信息（一词一行，编码格式为 UTF-8）

![dkbot3_04](http://www.dengke.xyz/usr/uploads/2019/11/4226896199.png)

（2）打开链接：http://www.speech.cs.cmu.edu/tools/lmtool-new.html，根据提示上传 txt 文件，并编译生成词库文件。

![dkbot3_05](http://www.dengke.xyz/usr/uploads/2019/11/2636617996.png)

（3）下载生成文件中的压缩包，解压至树莓派中 pocketsphinx/model/lm 目录

![dkbot3_06](http://www.dengke.xyz/usr/uploads/2019/11/1964547980.png)

# 第 3 章 开发与测试

## 3.1 修改节点文件

pocketsphinx 包默认并不兼容 ROS kinetic、最新语音模型和离线词库。因此需修改节点文件：recognizer.py、voice_cmd_vel.py。

**（1）recognizer.py：**

* 添加 hmm 参数：
  ![dkbot3_07](http://www.dengke.xyz/usr/uploads/2019/11/277002842.png)
* 取消使用默认设置：
  ![dkbot3_08](http://www.dengke.xyz/usr/uploads/2019/11/12509938.png)
* 增加 hmm 读取：
  ![dkbot3_09](http://www.dengke.xyz/usr/uploads/2019/11/1295465168.png)
* 设置 hmm 参数：
  ![dkbot3_10](http://www.dengke.xyz/usr/uploads/2019/11/2847311325.png)

**（2）voice_cmd_vel.py：**

* 该文件设置机器人识别到相应单词后的操作，并发布到/cmd_vel 主题。
  ![dkbot3_11](http://www.dengke.xyz/usr/uploads/2019/11/2244444009.png)
* 对识别结果进行操作设置。控制基本运动，设置对应命令的角度度、线速度。
  ![dkbot3_12](http://www.dengke.xyz/usr/uploads/2019/11/25144431.png)

## 3.2 创建 launch 文件

创建 pocketsphinx/launch/robot_voice_cmd.launch 文件，添加两个修改后的节点，并加入参数：词库文件 lm、dic 以及语音模型 hmm。内容如下：

![dkbot3_13](http://www.dengke.xyz/usr/uploads/2019/11/1357208182.png)

## 3.3 测试

编译功能包，执行如下命令，即可通过语音控制机器人运动。

```shell
rolsaunch pocketsphinx robot_voice_cmd.launch
```

也可通过如下命令，查看每一次的语音识别结果。

```shell
rostopic echo /recognizer/output
```

运行后，DKbot 中各节点的交互情况如下：

（1）recognizer.py 节点负责语音识别工作，并将识别结果发布到/recognizer/output 主题；

（2）voice_cmd_vel.py 节点订阅/recognizer/output 主题中的消息，并将对应的操作设置发布到/cmd_vel 主题；

（3）机器人驱动层订阅/cmd_vel 主题中的消息，从而驱动机器人运动。

![dkbot3_14](http://www.dengke.xyz/usr/uploads/2019/11/550003248.png)
