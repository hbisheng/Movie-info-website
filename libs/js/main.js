var app = angular.module('cs143.project1c', ['ui.bootstrap', 'ngRoute', 'ngAnimate', 'ngSanitize', 'blockUI']);

app.config( function($routeProvider) {
	$routeProvider
	.when("/", 
		{
			templateUrl : "template/search.html",
			controller: "search_control"
		}
	).when("/search/:keyword", 
			{
		templateUrl : "template/search.html",
		controller: "search_control"
	}).when("/add_actor",
		{
			templateUrl : "template/add_actor.html",
			controller: "add_actor_control"
		}
	).when("/add_movie",
		{
			templateUrl : "template/add_movie.html",
			controller: "add_movie_control"
		}
	).when("/add_actor_to_movie",
		{
			templateUrl : "template/add_actor_to_movie.html",
			controller: "add_actor_to_movie_control"
		}
	).when("/add_director_to_movie",
		{
			templateUrl : "template/add_director_to_movie.html",
			controller: "add_director_to_movie_control"
		}
	).when("/movie/:movieid",
		{
			templateUrl : "template/movie.html",
			controller: "movie_control"
		}
	).when("/person/:personid",
		{
			templateUrl : "template/person.html",
			controller: "person_control"
		}
	);
});


app.controller(
	'search_control', 
	function ($scope, $http, $location, $routeParams, $route) {
		$scope.currentKeyword = null;
		$scope.searched_word = null;
		
		$scope.search_keyword = function(keyword) {
			// Ask backend for data
			$http(
				{
					params: { 
						"keyword": keyword,
						"request_category": JSON.stringify({"actors":1, "movies":1})
					},
					method : "GET",
					url: "server/searchdb.php"
				}
			).then(
				function success(response) {
					$scope.actors = response.data.actors;
					$scope.movies = response.data.movies;
					$scope.searched_word = keyword;
				},
				function error(response) {
					$scope.errorMsg = "Error with this keyword";
					$scope.searched_word = keyword;
				}
			);
		}

		$scope.submit = function() {
			// validate and search
			if($scope.keyword != null && $scope.keyword.trim().length > 0 ) {
				$scope.errorMsg = null;
				
				if($scope.currentKeyword == $scope.keyword) {
					$route.reload();
				} else {
					$location.path("/search/"+$scope.keyword);
				}
				
				$scope.search_keyword($scope.keyword);
			} else {
				$scope.errorMsg = "Keyword is empty!";
				$scope.searched_word = null;
				$scope.actors = null;
				$scope.movies = null;
			}
		}
		
		if($routeParams.keyword != null) {
			$scope.keyword = $routeParams.keyword;
			$scope.currentKeyword = $scope.keyword;
			$scope.search_keyword($routeParams.keyword);
		}
	}
);

function isValidDate(d) {
	if ( Object.prototype.toString.call(d) === "[object Date]" ) {
		if(!isNaN(d.getTime())) { return true; }
	}
	return false;
}

app.controller("add_actor_control", function($scope, $http){
	$scope.msg = null;
	$scope.cnt = 0;
	
	$scope.add_person_alert = null;

	$scope.submit = function(){
		
		if(!$scope.firstname || $scope.firstname.trim().length == 0) {
			$scope.add_person_alert = {"type": "danger", "msg": "First name cannot be none"};
			return;
		}
		
		if($scope.firstname.length > 20) {
			$scope.add_person_alert = {"type": "danger", "msg": "First name should be less than 20 characters"};
			return;
		}

		if(!$scope.lastname || $scope.lastname.trim().length == 0) {
			$scope.add_person_alert = {"type": "danger", "msg": "Last name cannot be none"};
			return;
		}

		if($scope.lastname.length > 20) {
			$scope.add_person_alert = {"type": "danger", "msg": "Last name should be less than 20 characters"};
			return;
		}
		
		
		if(!isValidDate(new Date($scope.dob))) {
			$scope.add_person_alert = {"type": "danger", "msg": "Please specify a valid date of birth."};
			return;
		} 
		
		if($scope.dod) {
			if(!isValidDate(new Date($scope.dod))) {
				$scope.add_person_alert = {"type": "danger", "msg": "Please specify a valid date of death"};
				return;
			} 
			
			if(new Date($scope.dob) >= new Date($scope.dod)) {
				$scope.add_person_alert = {"type": "danger", "msg": "Date or birth should come before date of death"};
				return;
			}
		}
		$http(
			{
				params: 
				{
					"first": $scope.firstname,
					"last" : $scope.lastname,
					"gender" : $scope.gender,
					"role" : $scope.role,
					"dob"  : new Date($scope.dob).toISOString().slice(0, 10),
					"dod"  : $scope.dod ? new Date($scope.dod).toISOString().slice(0, 10) : null
				},
				method : "GET",
				url: "server/add_person.php"
			}
		).then(
			function success(response) {
				if(response.data.status == true) {
					$scope.add_person_alert = {"type": "success", "msg": "You just added a person into the database, with id = " + response.data.id};
				} else {
					$scope.add_person_alert = {"type": "danger", "msg": "We cannot add this person into the db."};
				}
			},
			function error(response) {
				$scope.add_person_alert = {"type": "danger", "msg": "We cannot add this person into the db."};
			}
		);
	}
});

app.controller("add_movie_control", function($scope, $http){
	$scope.rate_values = ["G", "NC-17", "PG", "PG-13", "R", "surrendere"]; 
	
	$scope.add_movie_alert = null;
	
	$scope.genreModel = { Drama: true, Comedy: false, Romance: false, Crime: false, Horror: false,
		Mystery: false, Thriller: false, Action: false, Adventure: false, Fantasy: false, 
		Documentary: false, Family: false, SciFi: false, Animation: false, Musical: false,
		War: false, Western: false, Adult: false, Short: false };

	$scope.genreResults = [];

	$scope.$watchCollection('genreModel', function () {
		$scope.genreResults = [];
			angular.forEach($scope.genreModel, function (value, key) {
				if (value) {
					$scope.genreResults.push(key);
				}
		});
	});
	
	$scope.submit = function(){
		if(!$scope.title || $scope.title.length > 100) {
			$scope.add_movie_alert = {"type": "danger", "msg": "Title must have a length between 1 and 100"};
			return;
		}
		
		if(!$scope.company || $scope.company.length > 50) {
			$scope.add_movie_alert = {"type": "danger", "msg": "Company must have a length between 1 and 50"};
			return;
		}
		
		if(!$scope.year || $scope.year > 9999 || $scope.year < 0) {
			$scope.add_movie_alert = {"type": "danger", "msg": "Please input a valid year"};
			return;
		}
		
		if(!$scope.genreResults || $scope.genreResults.length == 0) {
			$scope.add_movie_alert = {"type": "danger", "msg": "Please select at least one genre"};
			return;
		}
		
		$http(
			{
				params: 
				{
					"title": $scope.title,
					"company" : $scope.company,
					"rating" : $scope.rating,
					"year" : $scope.year,
					"genres" : JSON.stringify($scope.genreResults)
				},
				method : "GET",
				url: "server/add_movie.php"
			}
		).then(
			function success(response) {
				if(response.data.status == true) {
					$scope.add_movie_alert = {"type": "success", "msg": "You just added a movie into the database, with id = " + response.data.id};
				} else {
					$scope.add_movie_alert = {"type": "danger", "msg": "We cannot add this movie into the db."};
				}
			},
			function error(response) {
				$scope.add_movie_alert = {"type": "danger", "msg": "We cannot add this movie into the db."};
			}
		);
	}
});



app.controller("movie_control", function($scope, $http, $routeParams) {
	$scope.refresh_page = function() {
		$http(
			{
				params: { "id": $routeParams.movieid },
				method : "GET",
				url: "server/fetch_movie.php"
			}
		).then(
			function success(response) {
				$scope.error_msg = null;
				$scope.movie  = response.data.movie;
				$scope.actors = response.data.actors;
				$scope.directors = response.data.directors;
				$scope.genres = response.data.genres;
				$scope.comments = response.data.comments;
				if($scope.comments != null && $scope.comments.length != 0) {
					var sum = 0;
					for( var i = 0; i < $scope.comments.length; i++ ){
						sum += parseInt( $scope.comments[i].rating, 10 ); //don't forget to add the base
					}
					
					$scope.aver_score = parseFloat(1.0*sum/$scope.comments.length).toFixed(2);
					console.log($scope.aver_score);
				}
				if($scope.movie == null) {
					$scope.error_msg = "No matching movie in the database!";
				}
			},
			function error(response) {
				$scope.error_msg = "Error with this query!";
			}
		);
	}

	$scope.refresh_page();
	
	$scope.submit_comment = function() {
		if($scope.user_name == null || $scope.user_name.trim().length == 0) {
			$scope.user_review_alert = {"type":"warning", "msg": "Please leave your name" };
			return;
		}
		
		if($scope.user_name.length > 20) {
			$scope.user_review_alert = {"type":"danger", "msg": "Your name should be less than 20 characters." };
			return;
		}
		
		if($scope.user_comment == null || $scope.user_comment.trim().length == 0) {
			$scope.user_review_alert = {"type":"danger", "msg": "Please leave some comments." };
			return;
		}


		if($scope.user_comment != null && $scope.user_comment.length > 500) {
			$scope.user_review_alert = {"type":"danger", "msg": "Your comment should be less than 500 characters." };
			return;
		}
		
		$http({
				params: { 
					"user_name": $scope.user_name, 
					"user_comment" : $scope.user_comment, 
					"user_rate" : $scope.rate,
					"movie_id"	: $scope.movie.id
				},
				method : "GET",
				url: "server/add_comment.php"
			}
			).then(
			function success(response) {
				if(response.data.status == true) {
					$scope.refresh_page();
					$scope.user_review_alert = 
						{"type":"success", "msg": "Thank you " + $scope.user_name + ", your comment has been posted." };;
					$scope.user_name = "";
					$scope.user_comment = "";
					$scope.rate = 5;
				} else {
					$scope.user_review_alert = {"type":"danger", "msg": "We cannot process your request. Please check your input." };
				}
			},
			function error(response) {
				$scope.user_review_alert = {"type":"danger", "msg": "We cannot process your request. Please check your input." };
			}
		);
	}
});



app.controller("person_control", function($scope, $http, $routeParams) {
	$http(
		{
			params: { "id": $routeParams.personid },
			method : "GET",
			url: "server/fetch_person.php"
		}
	).then(
		function success(response) {
			$scope.error_msg = null;
			
			// director
			// movies_with_director
			$scope.actor  = response.data.actor;
			$scope.movies_with_actor = response.data.movies_with_actor;
			if($scope.actor == null) {
				$scope.error_msg = "No matching actor in the database!";
			}
		},
		function error(response) {
			$scope.error_msg = "Error with this query!";
		}
	);
});


app.controller("add_actor_to_movie_control", function($scope, $http) {
	$scope.add_relation = function() {
		if(!$scope.actor_info) {
			$scope.add_relation_alert = {"type":"warning", "msg": "Please search and choose an actor." };
			return;
		}
		
		if(!$scope.movie_info) {
			$scope.add_relation_alert = {"type":"warning", "msg": "Please search and choose a movie." };
			return;
		}
		
		if(!$scope.relation_role) {
			$scope.add_relation_alert = {"type":"warning", "msg": "Please enter a role." };
			return;
		}
		
		$http (
			{
				params: { 
					"aid": $scope.actor_info.id,
					"mid": $scope.movie_info.id,
					"role": $scope.relation_role
				},
				method : "GET",
				url: "server/add_actor_to_movie.php"
			}
		).then(
			function success(response) {
				if(response.data.status == true) {
					$scope.add_relation_alert = {"type":"success", "msg": "Relation added! " + $scope.actor_info.first + ' ' + $scope.actor_info.last + ' -> ' + $scope.movie_info.title};
				} else {
					$scope.add_relation_alert = {"type":"danger", "msg": "This relation already exists!" };
				}
			},
			function error(response) {
				$scope.add_relation_alert = {"type":"danger", "msg": "This relation already exists!" };
			}
		);
	}
	
	$scope.search_actor = function() {
		if($scope.actor_keyword) {
			$http (
				{
					params: { 
						"keyword": $scope.actor_keyword,
						"request_category": JSON.stringify({"actors":1})
					},
					method : "GET",
					url: "server/searchdb.php"
				}
			).then(
				function success(response) {
					$scope.actors = response.data.actors;
					$scope.searched_actor_keyword = $scope.actor_keyword;
				},
				function error(response) {
					$scope.searched_actor_keyword = $scope.actor_keyword;
				}
			);
		}
	}
	
	$scope.search_movie = function() {
		if($scope.movie_keyword) {
			$http (
				{
					params: { 
						"keyword": $scope.movie_keyword,
						"request_category": JSON.stringify({"movies":1})
					},
					method : "GET",
					url: "server/searchdb.php"
				}
			).then(
				function success(response) {
					$scope.movies = response.data.movies;
					$scope.searched_movie_keyword = $scope.movie_keyword;
				},
				function error(response) {
					$scope.searched_movie_keyword = $scope.movie_keyword;
				}
			);
		}
	}
	
	$scope.select_actor = function($index) {
		$scope.actor_info = $scope.actors[$index];
	}
	
	$scope.select_movie = function($index) {
		$scope.movie_info = $scope.movies[$index];
	}
});


app.controller("add_director_to_movie_control", function($scope, $http) {

	$scope.add_relation = function() {
		if(!$scope.director_info) {
			$scope.add_relation_alert = {"type":"warning", "msg": "Please search and choose an director." };
			return;
		}
		
		if(!$scope.movie_info) {
			$scope.add_relation_alert = {"type":"warning", "msg": "Please search and choose a movie." };
			return;
		}
		
		$http (
			{
				params: { 
					"did": $scope.director_info.id,
					"mid": $scope.movie_info.id
				},
				method : "GET",
				url: "server/add_director_to_movie.php"
			}
		).then(
			function success(response) {
				if(response.data.status == true) {
					$scope.add_relation_alert = {"type":"success", "msg": "Relation added! " + $scope.director_info.first + ' ' + $scope.director_info.last + ' -> ' + $scope.movie_info.title};
				} else {
					$scope.add_relation_alert = {"type":"danger", "msg": "This relation already exists!" };
				}
			},
			function error(response) {
				$scope.add_relation_alert = {"type":"danger", "msg": "This relation already exists!" };
			}
		);
	}
	
	$scope.search_director = function() {
		if($scope.director_keyword) {
			$http (
				{
					params: { 
						"keyword": $scope.director_keyword,
						"request_category": JSON.stringify({"directors":1})
					},
					method : "GET",
					url: "server/searchdb.php"
				}
			).then(
				function success(response) {
					$scope.directors = response.data.directors;
					$scope.searched_director_keyword = $scope.director_keyword;
				},
				function error(response) {
					$scope.searched_director_keyword = $scope.director_keyword;
				}
			);
		}
	}
	
	$scope.search_movie = function() {
		if($scope.movie_keyword) {
			$http (
				{
					params: { 
						"keyword": $scope.movie_keyword,
						"request_category": JSON.stringify({"movies":1})
					},
					method : "GET",
					url: "server/searchdb.php"
				}
			).then(
				function success(response) {
					$scope.movies = response.data.movies;
					$scope.searched_movie_keyword = $scope.movie_keyword;
				},
				function error(response) {
					$scope.searched_movie_keyword = $scope.movie_keyword;
				}
			);
		}
	}
	
	$scope.select_director = function($index) {
		$scope.director_info = $scope.directors[$index];
	}
	
	$scope.select_movie = function($index) {
		$scope.movie_info = $scope.movies[$index];
	}
});
