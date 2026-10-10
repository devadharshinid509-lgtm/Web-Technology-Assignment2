package com.portal;

import java.io.IOException;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

@WebServlet("/login")
public class LoginServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request,
                          HttpServletResponse response)
            throws ServletException, IOException {

        String username = request.getParameter("username");
        String password = request.getParameter("password");

        // Demo login credentials
        if ("admin".equals(username) &&
            "admin123".equals(password)) {

            HttpSession session = request.getSession();
            session.setAttribute("loggedIn", true);
            session.setAttribute("username", username);

            response.sendRedirect(
                request.getContextPath() + "/profiles"
            );

        } else {
            response.sendRedirect(
                request.getContextPath() + "/login.jsp?error=1"
            );
        }
    }
}
