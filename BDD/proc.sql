--creation dun user
CREATE OR REPLACE PROCEDURE add_user (
    p_email          IN VARCHAR2,
    p_first_name     IN VARCHAR2,
    p_last_name      IN VARCHAR2,
    p_password_hash  IN VARCHAR2,
    p_address        IN VARCHAR2 DEFAULT NULL,
    p_city           IN VARCHAR2 DEFAULT NULL,
    p_country        IN VARCHAR2 DEFAULT NULL,
    p_phone          IN VARCHAR2 DEFAULT NULL,
    p_is_admin       IN NUMBER DEFAULT 0,
    p_status         OUT NUMBER,  -- 1=success, 0=error
    p_message        OUT VARCHAR2
)
AS
    v_email_exists NUMBER;
BEGIN
    -- Initialize outputs
    p_status := 0;
    p_message := 'Unknown error';
    
    -- Check if email already exists
    SELECT COUNT(*)
    INTO v_email_exists
    FROM users
    WHERE email = p_email;
    
    IF v_email_exists > 0 THEN
        p_message := 'Email already exists';
        RETURN;
    END IF;
    
    -- Validate admin flag
    IF p_is_admin NOT IN (0, 1) THEN
        p_message := 'Invalid admin flag (must be 0 or 1)';
        RETURN;
    END IF;
    
    -- Insert the new user
    INSERT INTO users (
        email,
        first_name,
        last_name,
        password_hash,
        address,
        city,
        country,
        phone,
        is_admin
    )
    VALUES (
        p_email,
        p_first_name,
        p_last_name,
        p_password_hash,
        p_address,
        p_city,
        p_country,
        p_phone,
        p_is_admin
    )
    RETURNING user_id INTO p_user_id;
    
    -- Set success status
    p_status := 1;
    p_message := 'User created successfully';
    
    COMMIT;
EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        p_message := 'Error creating user: ' || SQLERRM;
END add_user;
/




--authentification
CREATE OR REPLACE PROCEDURE authenticate_user (
    p_email          IN VARCHAR2,
    p_password_hash  IN VARCHAR2,
    p_user_id        OUT NUMBER,
    p_first_name     OUT VARCHAR2,
    p_last_name      OUT VARCHAR2,
    p_is_admin       OUT NUMBER,
    p_status         OUT NUMBER,  -- 1=success, 0=error
    p_message        OUT VARCHAR2
)
AS
    v_stored_hash VARCHAR2(255);
BEGIN
    -- Initialize outputs
    p_status := 0;
    p_message := 'Authentication failed';
    p_user_id := NULL;
    
    -- Get user data if email exists
    BEGIN
        SELECT 
            user_id, 
            password_hash, 
            first_name, 
            last_name, 
            is_admin
        INTO
            p_user_id,
            v_stored_hash,
            p_first_name,
            p_last_name,
            p_is_admin
        FROM users
        WHERE email = p_email;
        
        -- Compare password hashes
        IF v_stored_hash = p_password_hash THEN
            p_status := 1;
            p_message := 'Authentication successful';
        ELSE
            p_message := 'Incorrect password';
        END IF;
        
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_message := 'Email not found';
    END;
END authenticate_user;
/


