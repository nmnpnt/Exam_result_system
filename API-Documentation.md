# Exam Result System - API Documentation

This document outlines the core REST API endpoints used in the Exam Result System.

---

## 1. Get All Results (Read Replica)
Fetches the fully computed grades for students.

*   **URL:** `/api/results`
*   **Method:** `GET`
*   **Success Response:**
    *   **Code:** `200 OK`
    *   **Content:**
```json
{
  "data": [
    {
      "id": 1,
      "enrollment_id": 12,
      "student": {
        "roll_number": "CSE2026001",
        "name": "John Doe"
      },
      "examination_course": {
        "course": {
          "code": "CS301",
          "title": "Database Systems"
        }
      },
      "total_marks_obtained": "85.50",
      "percentage": "85.50",
      "grade": "A+",
      "status": "published"
    }
  ]
}
```

---

## 2. Manual Marks Entry
Allows a teacher to manually insert or update a mark for a specific student, course, and assessment.

*   **URL:** `/api/marks`
*   **Method:** `POST`
*   **Payload:**
```json
{
    "student_roll_number": "CSE2026001",
    "course_code": "CS301",
    "assessment_component": "Internal",
    "marks": 28.5
}
```
*   **Success Response:**
    *   **Code:** `200 OK`
    *   **Content:** `{ "message": "Mark added/updated and re-computation queued." }`

---

## 3. Bulk CSV Upload
Uploads a large CSV file of marks and queues it for asynchronous processing.

*   **URL:** `/api/examinations/{examination_id}/marks/upload`
*   **Method:** `POST`
*   **Content-Type:** `multipart/form-data`
*   **Payload:**
    *   `file`: The `.csv` file.
*   **Success Response:**
    *   **Code:** `202 Accepted`
    *   **Content:** `{ "batch_id": "9a3f2b...", "message": "Upload queued." }`

---

## 4. Check Upload Progress
Poll this endpoint using the `batch_id` to get the live progress of the CSV chunking.

*   **URL:** `/api/mark-uploads/{batch_id}`
*   **Method:** `GET`
*   **Success Response:**
    *   **Code:** `200 OK`
    *   **Content:**
```json
{
    "id": "9a3f2b...",
    "progress": 65,
    "processed_jobs": 65,
    "total_jobs": 100,
    "failed_jobs": 0
}
```

---

## 5. Compute All Grades
Triggers the fan-out architecture to recalculate grades for every single student enrolled in the examination.

*   **URL:** `/api/examinations/{examination_id}/results/compute`
*   **Method:** `POST`
*   **Success Response:**
    *   **Code:** `202 Accepted`
    *   **Content:** `{ "message": "Result computation jobs dispatched for all enrollments." }`
