---
name: PSB Foundation Implementation
overview: Implement the PSB (Penerimaan Siswa Baru) foundation including database migrations, Eloquent models, PsbService, public registration controller, and Vue pages for landing, multi-step registration form, success, and tracking pages.
todos:
  - id: migrations
    content: "Create database migrations: academic_years, psb_settings, psb_registrations, psb_documents, psb_payments"
    status: completed
  - id: models
    content: "Create Eloquent models with relationships, casts, and factories: AcademicYear, PsbSetting, PsbRegistration, PsbDocument, PsbPayment"
    status: completed
  - id: service
    content: Create PsbService with generateRegistrationNumber, submitRegistration, uploadDocument, getRegistrationStatus, isRegistrationOpen, getActiveSettings
    status: completed
  - id: controller-routes
    content: Create PsbController and register PSB routes in web.php, create StorePsbRegistrationRequest
    status: completed
  - id: vue-landing
    content: Create PSB Landing page (resources/js/pages/Psb/Landing.vue) with hero, timeline, requirements list
    status: completed
  - id: vue-register
    content: Create multi-step registration form (Register.vue) with PsbMultiStepForm and PsbStepIndicator components
    status: completed
  - id: vue-success-tracking
    content: Create Success.vue and Tracking.vue pages with PsbTimeline component
    status: completed
  - id: tests
    content: Create unit tests for PsbService and feature tests for registration flow
    status: completed
  - id: build
    content: Run php artisan wayfinder:generate and yarn run build
    status: completed
isProject: false
---

# Epic 1: PSB Foundation & Public Registration Implementation

## Overview

Implementasi sistem PSB (Penerimaan Siswa Baru) untuk pendaftaran publik calon siswa baru, yaitu: database structure, business logic service, public-facing controller, dan Vue pages untuk landing, form pendaftaran multi-step, success page, dan tracking status.

---

## Architecture

```mermaid
flowchart TB
    subgraph Public["Public Routes (No Auth)"]
        Landing["/psb - Landing Page"]
        Register["/psb/register - Form Multi-Step"]
        Success["/psb/success/{id} - Success Page"]
        Tracking["/psb/tracking - Status Check"]
    end

    subgraph Backend["Backend Layer"]
        Controller["PsbController"]
        Service["PsbService"]
        FormRequest["StorePsbRegistrationRequest"]
    end

    subgraph Models["Data Layer"]
        AcademicYear["AcademicYear"]
        PsbSetting["PsbSetting"]
        PsbRegistration["PsbRegistration"]
        PsbDocument["PsbDocument"]
        PsbPayment["PsbPayment"]
    end

    Landing --> Controller
    Register --> FormRequest --> Controller
    Success --> Controller
    Tracking --> Controller
    Controller --> Service
    Service --> Models

    PsbSetting -->|belongsTo| AcademicYear
    PsbRegistration -->|hasMany| PsbDocument
    PsbRegistration -->|hasMany| PsbPayment
```



