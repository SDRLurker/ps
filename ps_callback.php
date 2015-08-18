<?php
/**
* 마이피플 봇 API 코드 샘플입니다. 
* 
* 마이피플 봇 API를 사용방법에 대해 안내합니다. 
* 알림콜백을 받은 뒤 action값에 따라 처리하는 방식입니다. 
*
* PHP version 5.4.7
*
* @category   Mypopple Bot API
* @author     Daum DNA Lab (http://dna.daum.net)
* @copyright  Daum DNA Lab
* @link       http://dna.daum.net/apis/mypeople
* 
*/
include("db.php");

$API_URL_PREFIX = "https://apis.daum.net";
$MYPEOPLE_BOT_APIKEY = "";	// 마이피플 API 키
$API_URL_POSTFIX = "&apikey=" .$MYPEOPLE_BOT_APIKEY; 

switch($_POST['action']) {
	case "addBuddy":
		greetingMessageToBuddy();	// 봇을 친구로 등록한 사용자의 이름을 가져와 환영 메시지를 보냅니다.
		break;
	case "sendFromMessage":		
		parseMessageToBuddy();		// 메세지에 따라 숫자야구 게임을 진행합니다.
		break;
	case "createGroup":
		groupCreatedMessage();		// 그룹대화방이 생성되었을때 그룹대화를 만든사람과 대화에 참여한 친구들의 이름을 출력합니다.
		break;
	case "inviteToGroup":		
		groupGreetingMeesage();		// 그룹대화방에 친구가 새로 추가될경우 누가 누구를 초대했는지 출력합니다.
		break;
	case "exitFromGroup":	
		groupExitAlertMessage();	// 그룹대화방에서 친구가 나갔을 경우 정보를 출력합니다.
		break;
	case "sendFromGroup":		
		filterGroupMessage();		// 그룹대화방에서 메시지에 따라 숫자야구 게임을 진행합니다.
		break;
	case "helpFromMessage":
		helpMessageToBuddy();		// 도움말 메세지를 출력합니다.
		break;
	case "helpFromGroup":
		helpMessageToGroup();		// 그룹대화방에서 도움말 메세지를 출력합니다.
		break;
}

function greetingMessageToBuddy()
{
	$buddyId = $_POST['buddyId'];		// 봇을 친구 추가한 친구ID
	$msg = getBuddyName($buddyId). "님 안녕하세요. /문 을 입력하시면 ". getBuddyName($buddyId). "님께서 맞출 문제를 생성합니다.";

	sendMessage("buddy", $buddyId, $msg);
}

function helpMessageToBuddy()
{
	$buddyId = $_POST['buddyId'];			
	$msg = ".문 : 봇이 ". getBuddyName($buddyId). "님께서 맞출 문제를 생성합니다.\n (.문제 or /문 or /문제)\n .순위 : ". getBuddyName($buddyId) ."님의 순위와 기록을 봅니다. (.기록 or /순위 or /기록)\n홈페이지:http://sdr1982.hosting.bizfree.kr/ps/";

	sendMessage("buddy", $buddyId, $msg);
}

function helpMessageToGroup()
{
	$groupId = $_POST['groupId'];		
	$buddyId = $_POST['buddyId'];			
	$msg = ".문 : 봇이 이 그룹채팅 방에서 맞출 문제를 생성합니다. (.문제 or /문 or /문제)\n .순위 : 그룹채팅 방에서 ". getBuddyName($buddyId) ."님의 순위와 기록을 봅니다. (.기록 or /순위 or /기록)\n홈페이지:http://sdr1982.hosting.bizfree.kr/ps/";

	sendMessage("group", $groupId, $msg);
}


function parseMessageToBuddy()
{
	$buddyId = $_POST['buddyId'];		// 메시지를 보낸 친구ID
	$msg =  $_POST['content'];		// 메시지 내용

	if($msg == "시작" || $msg == "start")
		$msg = ".문";
	if($msg == "끝" || $msg == "stop")
		$msg = ".끝";
	if($msg == "도움말" || $msg == "help")
		$msg = ".도움말";

	if($msg[0] == '.' || $msg[0] == '/')
	{
		switch( substr($msg,1) )
		{
			case "도움말":
				helpMessageToBuddy();
				break;
			case "끝":
				$con = null;
				$query = "UPDATE ps SET start = NULL, que_num = NULL WHERE id='". $buddyId ."'";
				list($con, $result) = execute_db($con,$query);
				sendMessage("buddy", $buddyId,
							 "출제된 문제를 취소합니다\n .문을 입력하면 문제를 다시 출제할 수 있습니다.");
				close_db($con);
				break;
			case "문":
			case "문제":
				/* 현재 시간을 구해 $timestamp 변수에 저장합니다. */
				$now = microtime();
				$timestamps = explode(" ", $now);
				$timestamp = (double)$timestamps[0] + (double)$timestamps[1];

				/* 겹치지 않는 3자리의 숫자를 만듭니다. */
				srand ( (double)microtime()*1000000 );
				while(TRUE)
				{
					$thesame = 0;
					$digits = array();
					for($i=0;$i<3;$i++)
						array_push($digits, mt_rand(1,9));
					for($i=0;$i<2;$i++)
					{
						for($j=$i+1;$j<3;$j++)
						{
							if($digits[$i] == $digits[$j])
								$thesame = 1;
						}
					}
					if($thesame == 0)
						break;
				}
				for($que_num = 0, $i=0;$i<3;$i++)
					$que_num = $que_num + (pow(10,$i) * $digits[$i]);
				
				/* DB에 문제낸 사람($buddyId), 맞출 숫자($que_num), 문제 만든 시간($timestamp)을 기록합니다. */
				$con = null;
				$query = "SELECT id, start FROM ps WHERE id='". $buddyId ."'";
				list($con, $result, $num) = execute_db($con,$query);
				if($num == 1)
				{
					$row = mysql_fetch_row($result);
					if($row[1] != NULL)
					{
						sendMessage("buddy", $buddyId,
							 "이미 문제가 출제되어 있습니다.\n세자리 숫자를 입력하여 답을 맞춰주세요.");
						close_db($con);
						break;
					}
					else
					{
						$query = "UPDATE ps SET que_num='". $que_num ."', start=".$timestamp
							." WHERE id='". $buddyId ."'";
					}
				}
				else
				{
					$query = "INSERT INTO ps(id, que_num, start) VALUES ('"
						. $buddyId ."', '". $que_num . "', ". $timestamp.")";
				}
				list($con, $result) = execute_db($con,$query);

				close_db($con);
				sendMessage("buddy", $buddyId,
					 "문제가 출제되었습니다.\n세자리 숫자를 입력하여 답을 맞춰주세요.");
				break;
			case "순위":
			case "기록":
				/* 현재 사용자의 최고 기록 및 랭킹을 확인합니다. */
				$con = null;
				$query = "SELECT record FROM ps WHERE id='". $buddyId ."'";
				list($con, $result, $num) = execute_db($con,$query);
				if($num == 1)
				{
					$row = mysql_fetch_row($result);
					if($row[0] == null)
					{
						$msg = getBuddyName($buddyId). "님의 기록이 아직 없습니다.";
						break;
					}
					$record = $row[0];					
					$query = "SELECT count(*) FROM ps WHERE record < ". $row[0];
					list($con, $result, $num) = execute_db($con,$query);	
					$row = mysql_fetch_row($result);
					$ranking = (int)$row[0] + 1;
					$msg = getBuddyName($buddyId). "님의 기록은 ". (int)$record."초이고, 순위는 ". $ranking ."위 입니다.";
				}
				else
				{
					$msg = getBuddyName($buddyId). "님의 기록이 아직 없습니다.";
				}
				close_db($con);
				sendMessage("buddy", $buddyId, $msg);
				break;
			default:
				sendMessage("buddy", $buddyId,
					 "명령어를 잘못 입력하셨습니다.\n.도움말 명령을 참고하세요.");
				break;
		}	
	}
	// 만약 3자리의 숫자가 입력되었다면...
	if(strlen($msg) == 3 && intval($msg) != 0)
	{
		$valid = 1;
		for($i=0;$i<3;$i++)
		{
			if($msg[$i]<'1' || $msg[$i]>'9')
				$valid = 0;
		}
		for($i=0;$i<2;$i++)
		{					
			for($j=$i+1;$j<3;$j++)
			{
				if($msg[$i] == $msg[$j])
					$valid = 0;
			}
		}
		// 입력받은 숫자가 유효하다면...
		if($valid == 1)
		{	
			$question = 1;	
			$con = null;	
			$query = "SELECT id, start, que_num FROM ps WHERE id='". $buddyId ."'";
			list($con, $result, $num) = execute_db($con,$query);
			if($num == 1)
			{
				$row = mysql_fetch_row($result);
				if($row[2] == null)
				{
					$question = 0;
				}
				else
				{
					$que_num = $row[2];								
				}
			}
			else
			{
				$question = 0;
			
			}		
			if($question == 0)
			{
				sendMessage("buddy", $buddyId, "문제가 출제되지 않았습니다.\n .문을 입력하여 문제를 출제해 주세요. " );
				break;
			}

			// 입력받은 숫자를 판정합니다.
			$strike = 0;
			$ball = 0;
			for($i=0;$i<3;$i++)
			{
				$pt_num = strpos($que_num, $msg[$i]);
				if($pt_num !== false)
				{
					if($pt_num == $i)
						$strike++;
					else
						$ball++;
				}
			}
			$msg = "";
			if($strike>0)
				$msg = $strike." 스트라이크";
			if($ball>0)
				$msg = $msg." ".$ball." 볼";

			if($strike==0 && $ball==0)
				$msg = "3아웃 입니다.";
			else if($strike<3)
				$msg = $msg." 입니다.";
			else
			{
				// 정답을 맞추었을 경우 현재의 $timestamp와 
				// 문제를 만든 시간의 timestamp 값의 차를 구해 $diff를 구합니다.
				$now = microtime();
				$timestamps = explode(" ", $now);
				$timestamp = (double)$timestamps[0] + (double)$timestamps[1];
				$diff = $timestamp - (double)$row[1];

				$con = null;
				// 문제만든 시간과 만들었던 숫자값을 NULL로 초기화합니다.
				$query = "UPDATE ps SET start = NULL, que_num = NULL WHERE id='". $buddyId ."'";
				list($con, $result) = execute_db($con,$query);
				// 이전기록이 없거나 최단시간에 숫자를 맞추었을 경우 DB에 기록합니다.
				$query = "UPDATE ps SET record = ".$diff." WHERE id='". $buddyId ."' AND (record > ". $diff ." OR record IS NULL )";
				list($con, $result) = execute_db($con,$query);

				$msg = $msg." 정답 입니다. ".(int)$diff."초 만에 문제를 푸셨습니다.";
			}
				
			close_db($con);
			sendMessage("buddy", $buddyId, $msg);
		}
		else
		{
			sendMessage("buddy", $buddyId, "잘못 입력하셨습니다. 123과 같이 각 자리수를 다르게 입력하세요." );
		}
			
	}
}

function groupCreatedMessage()
{
	$buddyId = $_POST['buddyId'];		// 그룹 대화를 만든 친구 ID
	$content =  $_POST['content'];		// 그룹 대화방 친구 목록(json형태)
	$groupId = $_POST['groupId'];		// 그룹ID

	$buddys = json_decode($content, true);	
	$buddysName = "";
	foreach($buddys as  $key => $value)
	{
		$buddysName .= " " .getBuddyName($buddys[$key][buddyId]);		
	}

	// 그룹에 생성메시지 보내기
	$msg = getBuddyName($buddyId). "님이 새로운 그룹대화를 만들었습니다. 그룹멤버는 " .$buddysName. " 입니다.";
	sendMessage("group", $groupId, $msg);
}

function groupGreetingMeesage()
{
	$buddyId = $_POST['buddyId'];		// 그룹 대화방에 초대한 친구 ID
	$content =  $_POST['content'];		// 그룹 대화방에 초대된 친구 정보
	$groupId = $_POST['groupId'];		// 그룹ID

	$buddys = json_decode($content, true);	
	$buddysName = "";
	foreach($buddys as  $key => $value)
	{
		$buddysName .= " " .getBuddyName($buddys[$key][buddyId]);		
	}

	//그룹에 환영 메시지 보내기	
	$msg = getBuddyName($buddyId). "님께서 " .$buddysName. "님을 초대하였습니다.";
	sendMessage("group", $groupId, $msg);
}

function groupExitAlertMessage()
{
	$buddyId = $_POST['buddyId'];		//그룹 대화방을 나간 친구 ID
	$groupId = $_POST['groupId'];		//그룹 대화방ID

	//그룹에 퇴장알림 메시지 보내기
	$msg = "슬프게도..." .getBuddyName($buddyId). "님께서 우리를 떠나갔어요.";
	sendMessage("group", $groupId, $msg);
}
function filterGroupMessage()
{
	$groupId = $_POST['groupId'];	// 그룹 대화방ID
	$buddyId = $_POST['buddyId'];	// 그룹 대화방에서 메시지를 보낸 친구ID
	$msg = $_POST['content'];	// 메시지 내용


	// 퇴장처리를 합니다.
	if (strcmp($msg, '퇴장') == 0 || strcmp($msg, 'exit') == 0)
	{
		$con = null;
		$query = "DELETE FROM ps_group WHERE id='". $groupId ."'";
		list($con, $result) = execute_db($con,$query);
		exitGroup($groupId);	//그룹 대화방에서 봇이 퇴장합니다.
		close_db($con);
		return;
	}


	if($msg == "시작" || $msg == "start")
		$msg = ".문";
	if($msg == "도움말" || $msg == "help")
		$msg = ".도움말";

	if($msg[0] == '.' || $msg[0] == '/')
	{
		switch( substr($msg,1) )
		{
			case "도움말":
				helpMessageToGroup();
				break;
			case "문":
			case "문제":
				/* 현재 시간을 구해 $timestamp 변수에 저장합니다. */
				$now = microtime();
				$timestamps = explode(" ", $now);
				$timestamp = (double)$timestamps[0] + (double)$timestamps[1];

				/* 겹치지 않는 3자리의 숫자를 만듭니다. */
				srand ( (double)microtime()*1000000 );
				while(TRUE)
				{
					$thesame = 0;
					$digits = array();
					for($i=0;$i<3;$i++)
						array_push($digits, mt_rand(1,9));
					for($i=0;$i<2;$i++)
					{
						for($j=$i+1;$j<3;$j++)
						{
							if($digits[$i] == $digits[$j])
								$thesame = 1;
						}
					}
					if($thesame == 0)
						break;
				}
				for($que_num = 0, $i=0;$i<3;$i++)
					$que_num = $que_num + (pow(10,$i) * $digits[$i]);
				
				/* DB에 문제낸 그룹($groupId), 맞출 숫자($que_num), 문제를 만든 시간($timestamp)을 기록합니다. */
				/* 그룹은 별도로 ps_group 테이블을 사용합니다. */
				$con = null;				
				$query = "SELECT id, start FROM ps_group WHERE id='". $groupId ."'";
				list($con, $result, $num) = execute_db($con,$query);
				if($num == 1)
				{
					$row = mysql_fetch_row($result);
					if($row[1] != NULL)
					{
						sendMessage("group", $groupId,
							 "이미 문제가 출제되어 있습니다.\n세자리 숫자를 입력하여 답을 맞춰주세요.");
						close_db($con);
						break;
					}
					else
					{
						$query = "UPDATE ps_group SET que_num='". $que_num ."', start=".$timestamp
							." WHERE id='". $groupId ."'";
					}
				}
				else
				{
					$query = "INSERT INTO ps_group(id, que_num, start) VALUES ('"
						. $groupId ."', '". $que_num . "', ". $timestamp.")";
				}
				list($con, $result) = execute_db($con,$query);
				close_db($con);

				sendMessage("group", $groupId,
					 "문제가 출제되었습니다.\n세자리 숫자를 입력하여 답을 맞춰주세요.");
				break;
			case "순위":
			case "기록":
				$con = null;
				/* 현재 그룹의 최고 기록 및 랭킹을 확인합니다. */
				$query = "SELECT grp_record FROM ps WHERE id='". $buddyId ."'";
				list($con, $result, $num) = execute_db($con,$query);
				if($num == 1)
				{
					$row = mysql_fetch_row($result);
					if($row[0] == null)
					{
						$msg = getBuddyName($buddyId). "님의 기록이 아직 없습니다.";
						break;
					}
					$record = $row[0];	
					/* 그룹쿼리 변경 */				
					$query = "SELECT count(*) FROM ps WHERE grp_record < ". $row[0];
					list($con, $result, $num) = execute_db($con,$query);	
					$row = mysql_fetch_row($result);
					$ranking = (int)$row[0] + 1;
					$msg = getBuddyName($buddyId). "님의 기록은 ". (int)$record."초이고, 순위는 ". $ranking ."위 입니다.";
				}
				else
				{
					$msg = getBuddyName($buddyId). "님의 기록이 아직 없습니다.";
				}
				close_db($con);
				/* 그룹에게 메세지 전송 */
				sendMessage("group", $groupId, $msg);
				break;
			default:
				sendMessage("group", $groupId,
					 "명령어를 잘못 입력하셨습니다.\n.도움말 명령을 참고하세요.");
				break;
		}	
	}
	// 만약 3자리의 숫자가 입력되었다면...
	if(strlen($msg) == 3 && intval($msg) != 0)
	{
		$valid = 1;
		for($i=0;$i<3;$i++)
		{
			if($msg[$i]<'1' || $msg[$i]>'9')
				$valid = 0;
		}
		for($i=0;$i<2;$i++)
		{					
			for($j=$i+1;$j<3;$j++)
			{
				if($msg[$i] == $msg[$j])
					$valid = 0;
			}
		}
		// 입력받은 숫자가 유효하다면...
		if($valid == 1)
		{	
			$question = 1;	
			$con = null;	
			/* 그룹쿼리 변경 */
			$query = "SELECT id, start, que_num FROM ps_group WHERE id='". $groupId ."'";
			list($con, $result, $num) = execute_db($con,$query);
			if($num == 1)
			{
				$row = mysql_fetch_row($result);
				if($row[2] == null)
				{
					$question = 0;
				}
				else
				{
					$que_num = $row[2];								
				}
			}
			else
			{
				$question = 0;
			
			}		
			if($question == 0)
			{
				sendMessage("group", $groupId, "문제가 출제되지 않았습니다.\n .문을 입력하여 문제를 출제해 주세요. " );
				break;
			}

			// 입력받은 숫자를 판정합니다.
			$strike = 0;
			$ball = 0;
			for($i=0;$i<3;$i++)
			{
				$pt_num = strpos($que_num, $msg[$i]);
				if($pt_num !== false)
				{
					if($pt_num == $i)
						$strike++;
					else
						$ball++;
				}
			}
			$msg = "";
			if($strike>0)
				$msg = $strike." 스트라이크";
			if($ball>0)
				$msg = $msg." ".$ball." 볼";

			if($strike==0 && $ball==0)
				$msg = "3아웃 입니다.";
			else if($strike<3)
				$msg = $msg." 입니다.";
			else
			{
				// 정답을 맞추었을 경우 현재의 $timestamp와 
				// 문제를 만든 시간의 timestamp 값의 차를 구해 $diff를 구합니다.			
				$now = microtime();
				$timestamps = explode(" ", $now);
				$timestamp = (double)$timestamps[0] + (double)$timestamps[1];
				$diff = $timestamp - (double)$row[1];

				$con = null;
				// 문제를 만든 시간과 만들었던 숫자값을 NULL로 초기화합니다.
				$query = "UPDATE ps_group SET start = NULL, que_num = NULL WHERE id='". $groupId ."'";
				list($con, $result) = execute_db($con,$query);
				// 이전기록이 없거나 최단시간에 숫자를 맞추었을 경우 DB에 기록합니다.
				$query = "UPDATE ps SET grp_record = ".$diff." WHERE id='". $buddyId ."' AND (grp_record > ". $diff ." OR grp_record IS NULL )";
				list($con, $result) = execute_db($con,$query);

				$msg = $msg." 정답 입니다. ".(int)$diff."초 만에 문제를 푸셨습니다.";
			}
				
			close_db($con);
			/* 그룹에게 메세지 전송 */
			sendMessage("group", $groupId, $msg);
		}
		else
		{
			sendMessage("group", $groupId, "잘못 입력하셨습니다. 123과 같이 각 자리수를 다르게 입력하세요." );
		}
			
	}
}
function exitGroup($groupId)
{
	global $API_URL_PREFIX, $API_URL_POSTFIX;

	$url =  $API_URL_PREFIX."/mypeople/group/exit.xml?groupId=" .$groupId .$API_URL_POSTFIX;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result = curl_exec($ch);
	curl_close($ch);
}

function sendMessage($target, $targetId, $msg)
{
	global $API_URL_PREFIX, $API_URL_POSTFIX, $MYPEOPLE_BOT_APIKEY;

	//메시지 전송 url 지정
	$url =  $API_URL_PREFIX."/mypeople/" .$target. "/send.xml?apikey=" .$MYPEOPLE_BOT_APIKEY;

	//CR처리. \n 이 있을경우 에러남
	$msg = str_replace(array("\n",'\n'), "\r", $msg);		

	//파라미터 설정
	$postData = array();
	$postData[$target."Id"] = $targetId;
	$postData['content'] = $msg;		
	$postVars = http_build_query($postData);

	//cURL을 이용한 POST전송
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postVars);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result = curl_exec($ch);
	curl_close($ch);

	//결과 출력
	echo "sendMessage";
	var_dump($result);
}

function getBuddyName($buddyId)
{
	global $API_URL_PREFIX, $API_URL_POSTFIX;	
	//프로필 정보보기 url 지정
	$url = $API_URL_PREFIX."/mypeople/profile/buddy.xml?buddyId=" .$buddyId .$API_URL_POSTFIX;

	//cURL을 통한 http요청
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result = curl_exec($ch);
	curl_close($ch);

	//결과 출력
	echo "getBuddyName";
	var_dump($result);

	//결과 파싱 및 리턴 
	$xml = simplexml_load_string($result);
	if ($xml->code == 200) {
		return $xml->buddys->name;
	} else {
		return null;		//오류
	}
}

function getIdsFromGroup($groupId)
{
	global $API_URL_PREFIX, $API_URL_POSTFIX;
	$url = $API_URL_PREFIX."/mypeople/group/members.json?groupId=" .$groupId .$API_URL_POSTFIX;

	//cURL을 통한 http요청
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result = curl_exec($ch);
	curl_close($ch);

	//결과 출력
	echo "getIdsFromGroup";
	var_dump($result);

	//결과 파싱 및 리턴 
	$buddys = json_decode($result, true);
	$ids = Array();
	foreach($buddys as $key => $value)
	{
		array_push($buddys[$key][buddyId]);
	}
	if ($xml->code == 200) {
		return $ids;
	} else {
		return null;		//오류
	}
}

?>
