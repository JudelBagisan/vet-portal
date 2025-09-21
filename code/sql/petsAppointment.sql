CREATE TABLE IF NOT EXISTS petsappointment (
    appointmentId INT AUTO_INCREMENT PRIMARY KEY,
    petOwnerId INT(8) UNSIGNED NOT NULL,
    petId INT(8) UNSIGNED NOT NULL,
    clinicId INT NOT NULL,
    appointDate DATE NOT NULL,
    appointTime TIME NOT NULL,
    purpose TEXT,
    qr_code VARCHAR(255) NULL,
    -- appointment_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    appointment_status VARCHAR(20) DEFAULT 'pending',
    clinicMessage TEXT,
    reason_for_reject TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (petOwnerId) REFERENCES pet_owner(petOwnerId) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (petId) REFERENCES pets(petId) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (clinicId) REFERENCES clinic(clinicId) ON DELETE CASCADE ON UPDATE CASCADE
) AUTO_INCREMENT = 100;


