package com.portal;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

@WebServlet("/file")
public class FileServlet extends HttpServlet {

    // Existing PHP project upload folder
    private static final Path UPLOAD_ROOT =
        Paths.get("C:/xampp/htdocs/student_admin/uploads")
             .toAbsolutePath().normalize();

    @Override
    protected void doGet(HttpServletRequest request,
                         HttpServletResponse response)
            throws ServletException, IOException {

        // Check login session
        HttpSession session = request.getSession(false);

        if (session == null ||
            !Boolean.TRUE.equals(session.getAttribute("loggedIn"))) {

            response.sendError(
                HttpServletResponse.SC_UNAUTHORIZED,
                "Please log in to access this file."
            );
            return;
        }

        // Read file ID from the URL
        int fileId;

        try {
            fileId = Integer.parseInt(request.getParameter("id"));
        } catch (Exception e) {
            response.sendError(
                HttpServletResponse.SC_BAD_REQUEST,
                "Invalid file ID."
            );
            return;
        }

        String relativePath;
        String fileType;

        // Fetch file information from MySQL
        String sql =
            "SELECT file_path, file_type FROM student_files WHERE id = ?";

        try (Connection con = DBConnection.getConnection();
             PreparedStatement stmt = con.prepareStatement(sql)) {

            stmt.setInt(1, fileId);

            try (ResultSet rs = stmt.executeQuery()) {

                if (!rs.next()) {
                    response.sendError(
                        HttpServletResponse.SC_NOT_FOUND,
                        "File not found."
                    );
                    return;
                }

                relativePath = rs.getString("file_path");
                fileType = rs.getString("file_type");
            }

        } catch (Exception e) {
            getServletContext().log("Database error while reading file", e);
            response.sendError(
                HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
                "Unable to retrieve file information."
            );
            return;
        }

        // Accept only files stored inside the uploads folder
        if (relativePath == null ||
            !relativePath.replace('\\', '/').startsWith("uploads/")) {

            response.sendError(
                HttpServletResponse.SC_FORBIDDEN,
                "Invalid file path."
            );
            return;
        }

        String fileName = relativePath.substring(
            relativePath.lastIndexOf('/') + 1
        );

        if (fileName.isEmpty() ||
            fileName.contains("..") ||
            fileName.contains(":")) {

            response.sendError(
                HttpServletResponse.SC_FORBIDDEN,
                "Invalid file name."
            );
            return;
        }

        Path target = UPLOAD_ROOT.resolve(fileName)
                                 .normalize();

        if (!target.startsWith(UPLOAD_ROOT) ||
            !Files.isRegularFile(target)) {

            response.sendError(
                HttpServletResponse.SC_NOT_FOUND,
                "File does not exist in storage."
            );
            return;
        }

        // Allow only PDF and common image formats
        if (!"application/pdf".equals(fileType) &&
            !"image/jpeg".equals(fileType) &&
            !"image/png".equals(fileType)) {

            response.sendError(
                HttpServletResponse.SC_UNSUPPORTED_MEDIA_TYPE,
                "This file type is not supported."
            );
            return;
        }

        // Stream the file to the browser
        response.setContentType(fileType);
        response.setHeader("X-Content-Type-Options", "nosniff");
        response.setHeader(
            "Content-Disposition",
            "inline; filename=\"" + fileName + "\""
        );
        response.setContentLengthLong(Files.size(target));

        Files.copy(target, response.getOutputStream());
    }
}
