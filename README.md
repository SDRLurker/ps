#마이피플 숫자야구 봇

마이피플 API는 2015년 6월 30일부로 종료된 상태입니다.
마이피플 API가 종료하기 전 운영하던 소스입니다.

## 파일목록

###callback.php

마이피플 API PHP용 샘플입니다.
https://github.com/daumdna/apis/tree/master/Samples/8.Mypeople/BotAPI/PHP

위의 github 소스 그대로입니다.

###db.php

데이터베이스를 처리하기 위한 함수들을 정의합니다.

###ps.sql

숫자야구 봇을 구동하기 위해 필요한 table SQL 파일입니다.

###ps_callback.php

숫자야구 봇을 구동하기 위해 필요한 callback 소스입니다.
마이피플 사용자가 숫자야구 봇에게 메세지를 보낼 경우 이 파일이 callback 형식으로 호출이 됩니다.