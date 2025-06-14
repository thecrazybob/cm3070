# Identity and Profile Management API

A flexible REST API that enables context-aware identity management, allowing users to maintain and present multiple sides of their identity based on who is asking and why.

## 🎯 Project Overview

This Laravel application demonstrates **Dynamic Context-Based Profile Retrieval** with requester-aware access control. Users can create multiple personas (contexts) and control exactly what information is shared with different requesters.

### Key Features
- **Context-Aware Profiles**: Users can have different profiles for different contexts (work, social, gaming, etc.)
- **Requester-Based Access Control**: API returns different data based on who's asking (owner, authenticated user, public)
- **Flexible Attribute System**: Support for custom profile attributes with visibility controls
- **Security-First Design**: Prevents information leakage and follows OWASP API security guidelines

## 🏗️ Architecture

- **Framework**: Laravel 12 with PHP 8.4
- **Database**: MySQL 8.0 with JSON support
- **Authentication**: Laravel Sanctum
- **API Documentation**: Scramble (OpenAPI/Swagger)
- **Testing**: PestPHP with stress testing

## 📄 License

This project is developed as part of academic coursework and is available for educational purposes.
