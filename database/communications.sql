-- Additive customer recovery / consent / SMS schema. Never drops existing data.
CREATE TABLE IF NOT EXISTS customer_preferences (
 user_id BIGINT UNSIGNED PRIMARY KEY, email VARCHAR(190) NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS communication_contacts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL,
 channel VARCHAR(10) NOT NULL, address VARCHAR(190) NOT NULL,
 status VARCHAR(20) NOT NULL DEFAULT 'pending', verified TINYINT NOT NULL DEFAULT 0,
 consent_source VARCHAR(40) NOT NULL, consent_at DATETIME NULL, withdrawn_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY channel_address(channel,address), KEY user_channel(user_id,channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS communication_otps (
 id CHAR(48) PRIMARY KEY, purpose VARCHAR(20) NOT NULL, user_id BIGINT UNSIGNED NULL,
 phone VARCHAR(20) NOT NULL, code_hash CHAR(64) NOT NULL, binding_hash CHAR(64) NOT NULL,
 password_marker CHAR(64) NOT NULL, attempts INT NOT NULL DEFAULT 0,
 state VARCHAR(20) NOT NULL DEFAULT 'queued', expires_at DATETIME NOT NULL,
 used_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 KEY phone_time(phone,created_at), KEY expires(expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS communication_campaigns (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_key CHAR(48) NOT NULL,
 title VARCHAR(100) NOT NULL, message TEXT NOT NULL, created_by BIGINT UNSIGNED NOT NULL,
 state VARCHAR(20) NOT NULL DEFAULT 'queued', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY campaign_key(request_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS communication_outbox (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, purpose VARCHAR(20) NOT NULL,
 otp_id CHAR(48) NULL, campaign_id BIGINT UNSIGNED NULL, contact_id BIGINT UNSIGNED NULL,
 recipient VARCHAR(20) NOT NULL, payload TEXT NULL, state VARCHAR(20) NOT NULL DEFAULT 'queued',
 provider_code VARCHAR(20) NULL, claimed_at DATETIME NULL, finished_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY campaign_contact(campaign_id,contact_id), KEY queue_state(state,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS communication_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, outbox_id BIGINT UNSIGNED NULL,
 purpose VARCHAR(20) NOT NULL, recipient_mask VARCHAR(30) NOT NULL,
 outcome VARCHAR(20) NOT NULL, provider_code VARCHAR(20) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, KEY log_time(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
