# Sprint 1-2 Implementation: Risk Monitoring & Notifications

## ✅ Completed Tasks

### 1. Database Models
- ✅ **RiskAlert** model (`app/models/risk_alert.py`)
  - Fields: id, project_id, document_id, alert_type, severity, title, description, source_text, recommendation, status, reviewed_by_id, reviewed_at, created_at
  - Enums: AlertType, RiskSeverity, RiskStatus
  - Relationships: project, document, reviewed_by (User)

- ✅ **Notification** model (`app/models/notification.py`)
  - Fields: id, user_id, notification_type, title, message, related_entity_type, related_entity_id, is_read, read_at, created_at
  - Enums: NotificationType, RelatedEntityType
  - Relationships: user

### 2. Database Migration
- ✅ Models added to `app/models/__init__.py`
- ✅ Models imported in `app/db/base.py` for Alembic discovery
- ⚠️ **Note**: Migration file creation requires database connection. Run:
  ```bash
  alembic revision --autogenerate -m "add_risk_alerts_and_notifications"
  alembic upgrade head
  ```

### 3. Pydantic Schemas
- ✅ **RiskAlert schemas** (`app/schemas/risk_alert.py`)
  - RiskAlertBase, RiskAlertCreate, RiskAlertUpdate, RiskAlertResponse

- ✅ **Notification schemas** (`app/schemas/notification.py`)
  - NotificationBase, NotificationCreate, NotificationUpdate, NotificationResponse

### 4. Services
- ✅ **RiskScannerService** (`app/services/risk_scanner.py`)
  - Keyword detection for 4 risk categories (financial_risk, compliance, market_change, operational)
  - Claude AI integration for risk validation and severity assessment
  - Methods: `scan_document()`, `scan_project()`, `_detect_keywords()`, `_analyze_with_claude()`

- ✅ **NotificationService** (`app/services/notification_service.py`)
  - Methods: `create_notification()`, `get_user_notifications()`, `mark_as_read()`, `delete_notification()`, `notify_risk_detected()`

### 5. API Endpoints

#### Council Router (Enhanced)
- ✅ `POST /api/v1/council/projects/{project_id}/scan-risks` - Manually trigger risk scan
- ✅ `GET /api/v1/council/projects/{project_id}/risks` - Get project risks (with filtering)
- ✅ `PUT /api/v1/council/risks/{risk_id}` - Update risk status
- ✅ `POST /api/v1/council/projects/{project_id}/insights` - Generate project insights

#### Notifications Router (New)
- ✅ `GET /api/v1/notifications/` - Get user notifications
- ✅ `PUT /api/v1/notifications/{notification_id}/read` - Mark as read
- ✅ `DELETE /api/v1/notifications/{notification_id}` - Delete notification

### 6. Background Tasks
- ✅ Auto-scanning on document upload
  - Added `scan_document_risks_background()` function
  - Automatically triggers after document upload
  - Creates notifications for detected risks

### 7. Integration
- ✅ Notifications router added to API v1 router
- ✅ All imports and dependencies configured

## 🔧 Fixed Issues

1. **Document model metadata conflict**: Renamed `metadata` field to `meta_data` (Python attribute) while keeping database column as `metadata`
2. **Document schema consistency**: Updated schemas to use `meta_data` instead of `metadata`
3. **Document CRUD fixes**: Updated to use correct field names (`file_name` instead of `filename`, `created_at` instead of `uploaded_at`)
4. **DocumentRead schema**: Replaced with `DocumentResponse` which actually exists

## 📋 Next Steps

### To Complete Setup:

1. **Create Database Migration**:
   ```bash
   cd backend
   alembic revision --autogenerate -m "add_risk_alerts_and_notifications"
   alembic upgrade head
   ```

2. **Set Environment Variable**:
   ```bash
   # In .env file or environment
   ANTHROPIC_API_KEY=your_api_key_here
   ```

3. **Test the Implementation**:
   - Start server: `uvicorn app.main:app --reload`
   - Upload a document via `/api/v1/council/upload`
   - Check for risk alerts via `/api/v1/council/projects/{project_id}/risks`
   - Check notifications via `/api/v1/notifications/`

## 🎯 Features Implemented

### Risk Detection Flow:
1. Document uploaded → Background task extracts text
2. Risk scanner detects keywords in text
3. For each keyword match, Claude AI validates if it's a real risk
4. Risk alerts created with severity and recommendations
5. Notifications sent to project owner

### Risk Management:
- View all risks for a project
- Filter by severity (low/medium/high/critical)
- Filter by status (new/reviewed/resolved/dismissed)
- Update risk status
- Get AI-generated recommendations

### Notifications:
- In-app notifications for risk alerts
- Mark as read functionality
- Delete notifications
- Filter by read/unread status

## 📝 API Usage Examples

### Scan Project for Risks:
```bash
POST /api/v1/council/projects/{project_id}/scan-risks
```

### Get Project Risks:
```bash
GET /api/v1/council/projects/{project_id}/risks?severity=high&status=new
```

### Update Risk Status:
```bash
PUT /api/v1/council/risks/{risk_id}
{
  "status": "reviewed",
  "recommendation": "Monitor quarterly financials"
}
```

### Get Notifications:
```bash
GET /api/v1/notifications/?unread=true&limit=20
```

## ⚠️ Important Notes

1. **Claude API Key**: Required for risk scanning. Set `ANTHROPIC_API_KEY` environment variable.
2. **Database**: PostgreSQL must be running and migrations applied.
3. **Multi-tenancy**: All queries are tenant-isolated via `current_user.tenant_id`.
4. **Background Tasks**: Risk scanning runs asynchronously after document upload.

## 🚀 Ready for Testing

All code is implemented and ready for testing. The system will:
- ✅ Automatically scan uploaded documents for risks
- ✅ Use Claude AI to validate and analyze risks
- ✅ Store risk alerts with severity levels
- ✅ Create in-app notifications for consultants
- ✅ Provide API to view/filter/update risk alerts
- ✅ Allow manual triggering of risk scans

---

**Status**: ✅ Sprint 1-2 Complete
**Date**: Implementation completed
**Next**: Sprint 3-4 - Build The Loom (Formula Engine)

