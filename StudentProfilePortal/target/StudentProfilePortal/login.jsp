<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile Portal - Login</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body>

    <div class="login-container">
        <h1>Student Profile Portal</h1>
        <p>Login to view student profiles</p>

        <% if ("1".equals(request.getParameter("error"))) { %>
            <p style="color:red;">Invalid username or password!</p>
        <% } %>

        <form action="${pageContext.request.contextPath}/login" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   placeholder="Enter username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Enter password" required>

            <button type="submit">Login</button>
        </form>
    </div>

</body>
</html>
