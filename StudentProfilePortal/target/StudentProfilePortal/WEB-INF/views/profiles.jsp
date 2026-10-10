<%@ page contentType="text/html;charset=UTF-8" %>
<%@ taglib prefix="c" uri="jakarta.tags.core" %>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Profiles</title>

    <link rel="stylesheet"
          href="${pageContext.request.contextPath}/css/style.css">
</head>
<body>

    <div class="container">

        <h1>Student Profile Portal</h1>

        <p>
            Welcome, <c:out value="${sessionScope.username}"/>!
            <a href="${pageContext.request.contextPath}/logout">Logout</a>
        </p>

        <h2>Student Details</h2>

        <c:choose>
            <c:when test="${empty students}">
                <p>No student records found.</p>
            </c:when>

            <c:otherwise>
                <c:forEach var="student" items="${students}">

                    <div class="student-card">

                        <h2><c:out value="${student.name}"/></h2>

                        <p>
                            <b>Roll Number:</b>
                            <c:out value="${student.rollNumber}"/>
                        </p>

                        <p>
                            <b>Email:</b>
                            <c:out value="${student.email}"/>
                        </p>

                        <p>
                            <b>Course:</b>
                            <c:out value="${student.course}"/>
                        </p>

                        <h3>Uploaded Images and Documents</h3>

                        <c:choose>
                            <c:when test="${empty student.files}">
                                <p>No uploaded files.</p>
                            </c:when>

                            <c:otherwise>
                                <c:forEach var="file" items="${student.files}">

                                    <div class="file-item">

                                        <c:if test="${file.type == 'image/jpeg' || file.type == 'image/png'}">
                                            <img
                                                src="${pageContext.request.contextPath}/file?id=${file.id}"
                                                alt="Student uploaded image"
                                                class="profile-image">
                                        </c:if>

                                        <p>
                                            <b>File Type:</b>
                                            <c:out value="${file.type}"/>
                                        </p>

                                        <p>
                                            <b>File Size:</b>
                                            <c:out value="${file.size}"/> bytes
                                        </p>

                                        <p>
                                            <b>Upload Date:</b>
                                            <c:out value="${file.date}"/>
                                        </p>

                                        <a href="${pageContext.request.contextPath}/file?id=${file.id}"
                                           target="_blank">
                                            View / Download File
                                        </a>

                                    </div>

                                </c:forEach>
                            </c:otherwise>
                        </c:choose>

                    </div>

                </c:forEach>
            </c:otherwise>
        </c:choose>

    </div>

</body>
</html>
