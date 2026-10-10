package com.portal;

import java.io.IOException;

import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

@WebServlet("/logout")
public class LogoutServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request,
                         HttpServletResponse response)
            throws IOException {

        // Get the existing session
        HttpSession session = request.getSession(false);

        // End the user session
        if (session != null) {
            session.invalidate();
        }

        // Redirect to the login page
        response.sendRedirect(
            request.getContextPath() + "/login.jsp"
        );
    }
}
