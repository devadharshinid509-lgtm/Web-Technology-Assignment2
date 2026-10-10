package com.portal;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

@WebServlet("/profiles")
public class StudentProfileServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request,
                         HttpServletResponse response)
            throws ServletException, IOException {

        // Check whether the user is logged in
        HttpSession session = request.getSession(false);

        if (session == null ||
            !Boolean.TRUE.equals(session.getAttribute("loggedIn"))) {

            response.sendRedirect(
                request.getContextPath() + "/login.jsp"
            );
            return;
        }

        List<Map<String, Object>> students = new ArrayList<>();

        String studentSQL =
            "SELECT id, name, roll_number, email, course " +
            "FROM students ORDER BY id";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement studentStmt =
                 con.prepareStatement(studentSQL);
             ResultSet studentRS = studentStmt.executeQuery()) {

            while (studentRS.next()) {

                Map<String, Object> student = new HashMap<>();

                int studentId = studentRS.getInt("id");

                student.put("id", studentId);
                student.put("name", studentRS.getString("name"));
                student.put("rollNumber",
                            studentRS.getString("roll_number"));
                student.put("email", studentRS.getString("email"));
                student.put("course", studentRS.getString("course"));

                // Retrieve files belonging to this student
                List<Map<String, Object>> files = new ArrayList<>();

                String fileSQL =
                    "SELECT id, file_path, file_type, file_size, upload_date " +
                    "FROM student_files WHERE student_id = ? ORDER BY id";

                try (PreparedStatement fileStmt =
                         con.prepareStatement(fileSQL)) {

                    fileStmt.setInt(1, studentId);

                    try (ResultSet fileRS = fileStmt.executeQuery()) {

                        while (fileRS.next()) {

                            Map<String, Object> file = new HashMap<>();

                            file.put("id", fileRS.getInt("id"));
                            file.put("path",
                                     fileRS.getString("file_path"));
                            file.put("type",
                                     fileRS.getString("file_type"));
                            file.put("size",
                                     fileRS.getInt("file_size"));
                            file.put("date",
                                     fileRS.getTimestamp("upload_date"));

                            files.add(file);
                        }
                    }
                }

                student.put("files", files);
                students.add(student);
            }

            // Send student data to the JSP page
            request.setAttribute("students", students);

            request.getRequestDispatcher(
                "/WEB-INF/views/profiles.jsp"
            ).forward(request, response);

        } catch (Exception e) {

            getServletContext().log(
                "Unable to load student profiles", e
            );

            response.sendError(
                HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
                "Unable to load student profiles. Please try again."
            );
        }
    }
}
