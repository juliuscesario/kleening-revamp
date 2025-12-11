# Attendance Module Requirements

## Background
- Building native attendance tracking directly inside the application so owners can monitor staff presence without relying on external vendors.
- Staff must clock in and out easily, while sharing their real-time geolocation to confirm work-site presence.
- Owners need oversight features: approval workflows, period recaps, and timestamp visibility in a single place.

## Functional Requirements

### Staff
- Perform clock-in and clock-out actions with automatic capture of `lat`, `lng`, `accuracy`, and timestamps.
- View current status indicators (e.g., *Working*, *Not Working*, *Missed Clock-out*).
- Review recent clock events with a location map preview for transparency.
- Follow a fallback flow when geolocation fails, supplying manual coordinates plus a selfie photo and note that will be flagged for owner review.

### Owner
- Configure geofences per service location (center coordinates plus configurable radius in meters).
- Approve or reject attendance records that fall outside the geofenced area.
- Generate attendance recaps for custom date ranges with export options (CSV/PDF).
- Inspect detailed logs including clock-in/out times, captured coordinates, approval status, and reviewer notes.
- Monitor a real-time dashboard that highlights which staff are currently clocked in and their locations.
- Receive alerts for pending approvals or missed clock-outs.

## Technical Requirements & Considerations
- **Data model**: Attendance log table storing clock type (`IN`/`OUT`), geolocation payload, accuracy, device info, approval state, reviewer notes, and optional fallback evidence (selfie path + note).
- **Geolocation**: Use HTML5 Geolocation API (request `enableHighAccuracy`). Persist raw coordinates and validate distances via server-side Haversine helper or spatial database features.
- **Radius checks**: Compare captured coordinates against configured radius, marking out-of-bounds entries as `pending` until reviewed.
- **APIs**:
  - `POST /attendance/clock-in` and `POST /attendance/clock-out` for staff submissions.
  - `PATCH /attendance/{id}/approve` and `PATCH /attendance/{id}/reject` for owner review actions.
  - `GET /attendance` with filters for date range, staff member, and approval status.
- **Security**: Enforce authentication, rate limiting, and payload integrity measures (e.g., signed payloads, device fingerprinting) to reduce spoofing.
- **Offline handling**: Allow local queueing of clock events when offline; sync and validate on reconnect while keeping status `pending` until verification.
- **Notifications**: Integrate with existing notification channels (push/email/SMS) for approval reminders, missing clock-out alerts, and optional real-time dashboard refreshes.
- **Audit**: Maintain an immutable history of submissions and approval actions to resolve disputes.
- **Data retention**: Store attendance and location data indefinitely unless manually purged; no mandated deletion workflow required.
- **Compliance**: No region-specific consent flows required beyond standard location permission prompts.

## To-Do List
1. Finalize data relationships between staff, service orders, and geofence configurations.
2. Define database schema updates for attendance logs, geofence settings, and fallback evidence storage (selfies + notes).
3. Draft API contracts and validation rules for clock events, approvals, and dashboard feeds.
4. Design staff UI flow (clock screen, status indicators, permission prompts, fallback evidence capture).
5. Design owner UI (review queue, recap view, real-time dashboard, exports).
6. Implement geolocation capture on the front-end with robust error handling and accuracy thresholds.
7. Implement server-side distance validation, approval workflow, and real-time status calculations.
8. Add reporting endpoints and export utilities for recaps.
9. Integrate reminder and alert notifications for approvals, missed clock-outs, and dashboard updates.
10. Plan QA coverage: automated tests (unit, feature), plus manual acceptance for geolocation and evidence flows.
