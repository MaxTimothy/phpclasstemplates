<?php
	require_once "DataObject.class.php";
	//could be what ever you want, like say a report :]
	class Name extends DataObject {

		protected $data = array(
			"id" => "",
			"username" =>"",
			"password" =>"",
			"firstName" =>"",
			"lastName" =>"",
			"joinDate" =>"",
			"gender" => "",
			"favoriteGenre" => "",
			"emailAddress" => "",
			"otherInterests" => ""
		);

		private $_genres = array(
			"crime" => "Crime",
			"horror" => "Horror",
			"thriller" =>"Thriller",
			"romance" => "Romance",
			"sciFi" => "Sci-Fi",
			"adventure" =>"Adventure",
			"nonFiction" => "Non-Fiction"
		);

		private $_views = array(
			"male" => "Male",
			"female" => "Female",
		);

		public static function getNames( $startRow, $numRows, $order ) {
			$conn = @parent::connect();
			$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . TBL_NAMES . " ORDER BY
			$order LIMIT :startRow, :numRows";

			try {
				$st = $conn-> prepare( $sql );
				$st-> bindValue( ":startRow", $startRow, PDO::PARAM_INT );
				$st-> bindValue( ":numRows", $numRows, PDO::PARAM_INT );
				$st-> execute();

				$members = array();
				foreach ( $st-> fetchAll() as $row ) {
					$members[] = new Member( $row );
				}
				$st = $conn-> query( "SELECT found_rows() AS totalRows" );
				$row = $st-> fetch();
				@parent::disconnect( $conn );
				return array( $members, $row["totalRows"] );
			} catch ( PDOException $e ) {
				@parent::disconnect( $conn );
				die( "Query failed: " . $e-> getMessage() );
			}
		}

		public static function getName( $id ) {
			$conn = @parent::connect();
			$sql = "SELECT * FROM " . TBL_NAMES . " WHERE id = :id";

			try {
				$st = $conn-> prepare( $sql );
				$st-> bindValue( ":id", $id, PDO::PARAM_INT );
				$st-> execute();
				$row = $st-> fetch();
				@parent::disconnect( $conn );
				if ( $row ) return new Member( $row );
			} catch ( PDOException $e ) {
				@parent::disconnect( $conn );
				die( "Query failed: " . $e-> getMessage() );
			}
		}

		public static function getByUsername( $username ) {
			$conn = parent::connect();
			$sql = "SELECT * FROM " . TBL_NAMES . " WHERE username = :username";

			try {
				$st = $conn-> prepare( $sql );
				$st-> bindValue( ":username", $username, PDO::PARAM_STR );
				$st-> execute();
				$row = $st-> fetch();
				parent::disconnect( $conn );
				if ( $row ) return new Member( $row );
			} catch ( PDOException $e ) {
				parent::disconnect( $conn );
				die( "Query failed: " . $e-> getMessage() );
			}
		}

		public static function getByEmailAddress( $emailAddress ) {
			$conn = parent::connect();
			$sql = "SELECT * FROM " . TBL_NAMES . " WHERE emailAddress =
			:emailAddress";

			try {
				$st = $conn-> prepare( $sql );
				$st-> bindValue( ":emailAddress", $emailAddress, PDO::PARAM_STR );
				$st-> execute();
				$row = $st-> fetch();
				parent::disconnect( $conn );
				if ( $row ) return new Member( $row );
			} catch ( PDOException $e ) {
				parent::disconnect( $conn );
				die( "Query failed: " . $e-> getMessage() );
			}
		}

		public static function getTotalPageViewsByGender($gender){
			$conn = @parent::connect();
			$sql = "SELECT SUM(log.numVisits) AS totViews".$gender." FROM ".TBL_ACCESS_LOG." AS log JOIN ".TBL_NAMES." AS mem ON log.memberId = mem.id WHERE mem.gender = :gender";

			try {
				$st = $conn-> prepare( $sql );
				$st-> bindValue( ":gender", $gender, PDO::PARAM_STR );
				$st-> execute();
				$tot = $st-> fetch();
				@parent::disconnect( $conn );
				return $tot["totViews".$gender];
			} catch ( PDOException $e ) {
				@parent::disconnect( $conn );
				die( "Query failed: " . $e-> getMessage() );
			}
		}

		public function getGenderString() {
			return ( $this-> data["gender"] == "f" ) ? "Female" : "Male";
		}

		public function getFavoriteGenreString() {
			return ( $this-> _genres[$this-> data["favoriteGenre"]] );
		}

		public function getGenres() {
			return $this-> _genres;
		}

		public function insert() {
			$conn = parent::connect();
			$sql = "INSERT INTO " . TBL_NAMES . " (
														username,
														password,
														firstName,
														lastName,
														joinDate,
														gender,
														favoriteGenre,
														emailAddress,
														otherInterests
														) VALUES (
														:username,
														password(:password),
														:firstName,
														:lastName,
														:joinDate,
														:gender,
														:favoriteGenre,
														:emailAddress,
														:otherInterests
														)";

			try {
				$st = $conn-> prepare( $sql );
				$st-> bindValue( ":username", $this-> data["username"], PDO::PARAM_STR );
				$st-> bindValue( ":password", $this-> data["password"], PDO::PARAM_STR );
				$st-> bindValue( ":firstName", $this-> data["firstName"], PDO::PARAM_STR );
				$st-> bindValue( ":lastName", $this-> data["lastName"], PDO::PARAM_STR );
				$st-> bindValue( ":joinDate", $this-> data["joinDate"], PDO::PARAM_STR );
				$st-> bindValue( ":gender", $this-> data["gender"], PDO::PARAM_STR );
				$st-> bindValue( ":favoriteGenre", $this-> data["favoriteGenre"],
				PDO::PARAM_STR );
				$st-> bindValue( ":emailAddress", $this-> data["emailAddress"],
				PDO::PARAM_STR );
				$st-> bindValue( ":otherInterests", $this-> data["otherInterests"],
				PDO::PARAM_STR );
				$st-> execute();
				parent::disconnect( $conn );
			} catch ( PDOException $e ) {
				parent::disconnect( $conn );
				die( "Query failed: " . $e-> getMessage() );
			}
		}

		public function authenticate() {
			$conn = parent::connect();
			$sql = "SELECT * FROM " . TBL_NAMES . " WHERE username = :username
			AND password = password(:password)";

			try {
				$st = $conn-> prepare( $sql );
				$st-> bindValue( ":username", $this-> data["username"], PDO::PARAM_STR );
				$st-> bindValue( ":password", $this-> data["password"], PDO::PARAM_STR );
				$st-> execute();
				$row = $st-> fetch();
				parent::disconnect( $conn );
				if ( $row ) return new Member( $row );
			} catch ( PDOException $e ) {
				parent::disconnect( $conn );
				die( "Query failed: " . $e-> getMessage() );
			}
		}

	}
?> 