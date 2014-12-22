var todoApp = angular.module('todoApp', []);

todoApp.controller('TodoAppCtrl', ['$scope', '$http', function($scope, $http) {
    $http.get('/api/todos').success(function(data) {
       $scope.todos = data;
    });

    $scope.addModel = function(text) {
        var todo = {text:text};
        $http.put('/api/todos', todo).success(function(data) {
            $scope.todos.push(data);
            $scope.addTodo = '';
        });
    }

    $scope.deleteModel = function(id) {
        $http.delete('/api/todos/' + id).success(function(data) {
            for (var todo in $scope.todos) {
                if ($scope.todos[todo].id == id) {
                    $scope.todos.splice(todo, 1);
                    break;
                }
            }
        });
    };

    $scope.toggleStatus = function(id, status) {
        $http.post('/api/todos/' + id, {status:status}).success(function(data) {

        });
    }
}]);

