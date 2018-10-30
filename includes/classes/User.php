<?php
	/**
	 * 
	 */
	class User {
		private $conn;
		private $username;
		private $user_details;
		function __construct($conn, $username){
			$this->conn = $conn;
			$this->username = $username;
			$query = mysqli_query($this->conn, "SELECT * FROM users WHERE username='$this->username'");
			$this->user_details = mysqli_fetch_array($query);
		}

		// return username of the user
		public function getUsername() {
			return $this->username;
		}

		// return user's "first_name last_name" text format
		public function getFirstAndLastName() {
			return $this->user_details['first_name'] . " " . $this->user_details['last_name'];
		}

		// return user bio
		public function getBio() {
			return $this->user_details['bio'];
		}

		// return coverpic
		public function getCoverPic() {
			return $this->user_details['cover_pic'];
		}

		// return first_name, last_name and profile_pic
		public function getUserLessInfo() {
			$query = mysqli_query($this->conn, "SELECT first_name, last_name, profile_pic, cover_pic, is_online FROM users WHERE username='$this->username'");
			return mysqli_fetch_array($query);
		}

		// return user's friend_array in text format
		public function getFriendArrayText() {
			$query = mysqli_query($this->conn, "SELECT friend_array FROM users WHERE username='$this->username'");
			$row = mysqli_fetch_array($query);
			return $row['friend_array'];
		}

		// return user's friend_array
		public function getFriendArray() {
			$friend_array = explode(",", $this->getFriendArrayText());
			return $friend_array;
		}

		// return number of friends
		public function getNumOfFriends() {
			return count($this->getFriendArray())-2;
		}

		// return weather user's account is closed or not
		public function isClosed() {
			$query = mysqli_query($this->conn, "SELECT deactivate_account FROM users WHERE username='$this->username'");
			$row = mysqli_fetch_array($query);
			if($row['deactivate_account'] == 0)
				return false;
			else 
				return true;
		}

		// return weather given username is user's friend or not
		public function isFriend($friend_username) {
			$friend_username = "," . $friend_username . ",";
			if(strstr($this->getFriendArrayText(), $friend_username)) {
				return true;
			} else {
				return false;
			}
		}

		// add friend to user's friend_array
		public function addFriend($friend_username) {
			$user_friend_array_text = $this->getFriendArrayText();

			$query = mysqli_query($this->conn, "SELECT friend_array FROM users WHERE username='$friend_username'");
			$row = mysqli_fetch_array($query);
			$friend_friend_array_text = $row['friend_array'];

			$user_friend_array_text = $user_friend_array_text . $friend_username . ",";
			$friend_added = mysqli_query($this->conn, "UPDATE users SET friend_array='$user_friend_array_text' WHERE username='$this->username'");

			$friend_friend_array_text = $friend_friend_array_text . $this->username . ",";
			$friend_added = mysqli_query($this->conn, "UPDATE users SET friend_array='$friend_friend_array_text' WHERE username='$friend_username'");
		}

		// remove username of user from friend's friend_array and vice-versa
		public function removeFriend($friend_username) {
			$user_friend_array_text = $this->getFriendArrayText();

			$query = mysqli_query($this->conn, "SELECT friend_array FROM users WHERE username='$friend_username'");
			$row = mysqli_fetch_array($query);
			$friend_friend_array_text = $row['friend_array'];

			$user_friend_array_text = str_replace($friend_username . ",", "", $user_friend_array_text);
			$remove_friend = mysqli_query($this->conn, "UPDATE users SET friend_array='$user_friend_array_text' WHERE username='$this->username'");

			$friend_friend_array_text = str_replace($this->username . ",", "", $friend_friend_array_text);
			$remove_friend = mysqli_query($this->conn, "UPDATE users SET friend_array='$friend_friend_array_text' WHERE username='$friend_username'");
		}

		// return numbers of mutual_friends
		public function getMutualFriendsCount($friend_username) {
			$mutual_friends = 0;
			$user_friend_array = getFriendArray();

			$friend = new User($conn, $friend_username);
			$friend_friend_array = $friend->getFriendArray();
			
			$mutual_friends = count(array_intersect($user_friend_array, $friend_friend_array));
			return $mutual_friends;
		}

		// search user where first_name and last_name like
		public function searchUsers($input_name) {
			$first_last_name = explode(" ", $input_name);
			if(strpos($query, "_") !== false) {
				$query = mysqli_query($conn, "SELECT * FROM users WHERE username LIKE '$first_last_name%' AND user_closed=0 LIMIT 8");
			} else if(count($names) == 2) {
				$query = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND user_closed=0 LIMIT 8");
			} else {
				$query = mysqli_query($conn, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%') AND user_closed=0 LIMIT 8");
			}
			return $query;
		}

		// return user details array
		public function userInfoArray() {
			$query = mysqli_query($this->conn, "SELECT * FROM users WHERE username='$this->username'");
			return mysqli_fetch_array($query);
		}


		// upload profile_pic of user
	  public function uploadCoverPic($file) {
		  $temp = explode(".", $file["name"]);
		  $target_dir = "assets/images/cover_pics/";
		  $newfilename = $this->username . round(microtime(true)) . '.' . end($temp);
		  $target_file = $target_dir . basename($newfilename);
		  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

		  $check = getimagesize($file["tmp_name"]);
		  if($check === false) {
		    return "File is not an image.";
		  }
		  if ($file["size"] > 200000) {
		    return "Sorry, your file is too large.";
		  }
		  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
		    return "Sorry, only JPG, JPEG & PNG files are allowed.";
		  }

		  // if everything is ok, try to upload file
		  if (move_uploaded_file($file["tmp_name"], $target_file)) {
		  	$query = mysqli_query($this->conn, "UPDATE users SET cover_pic='$target_file' WHERE username='$this->username'");
		  	if ($query) {
		    	return "The file ". basename( $file["name"]). " has been uploaded.";
		    }
		  }
		  return "Sorry, there was an error while uploading your file.";
		}

	  // upload cover_pic of user
		public function uploadProfilePic($file) {
		  $temp = explode(".", $file["name"]);
		  $target_dir = "assets/images/profile_pics/";
		  $newfilename = $this->username . round(microtime(true)) . '.' . end($temp);
		  $target_file = $target_dir . basename($newfilename);
		  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

		  $check = getimagesize($file["tmp_name"]);
		  if($check === false) {
		    return "File is not an image.";
		  }
		  if ($file["size"] > 200000) {
		    return "Sorry, your file is too large.";
		  }
		  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
		    return "Sorry, only JPG, JPEG & PNG files are allowed.";
		  }

		  // if everything is ok, try to upload file
		  if (move_uploaded_file($file["tmp_name"], $target_file)) {
		  	$query = mysqli_query($this->conn, "UPDATE users SET profile_pic='$target_file' WHERE username='$this->username'");
		  	if ($query) {
		    	return "The file ". basename( $file["name"]). " has been uploaded.";
		    }
		  }
		  return "Sorry, there was an error while uploading your file.";
		}

		// return number of posts by the user
		public function getNumOfPosts() {
			$query = mysqli_query($this->conn, "SELECT * FROM posts WHERE user_from='$this->username' and deleted=0");
			return mysqli_num_rows($query);
		}

		// add login_time of user
		public function setLogInTime() {
			$query = mysqli_query($this->conn, "INSERT INTO user_login_durations (username) VALUES ('$this->username')");
		}

		// update logout_time of user for last login
		public function setLogOutTime() {
			$query = mysqli_query($this->conn, "UPDATE user_login_durations SET logout_time=NOW() WHERE username='$this->username' ORDER BY id DESC LIMIT 1)");
		}
	}
?>