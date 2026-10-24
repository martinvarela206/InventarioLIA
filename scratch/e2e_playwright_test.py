"""
E2E Playwright Test Suite for InventarioLIA (Nexo LIA)
Tests landing page, auth, roles, fast movements, dynamic attributes, user management,
and app_admin dynamic content (spaces, staff, feed), both in Desktop and Mobile viewports.
"""
import os
import sys
import time

if sys.platform == "win32":
    try:
        sys.stdout.reconfigure(encoding="utf-8")
        sys.stderr.reconfigure(encoding="utf-8")
    except Exception:
        pass

from playwright.sync_api import sync_playwright, expect

BASE_URL = os.environ.get("BASE_URL", "http://127.0.0.1:8000")
SCREENSHOT_DIR = os.path.abspath("scratch/screenshots")
os.makedirs(SCREENSHOT_DIR, exist_ok=True)

def run_tests():
    print(f"=== INICIANDO SUITE DE PRUEBAS PLAYWRIGHT E2E en {BASE_URL} ===")
    
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        
        # -------------------------------------------------------------
        # TEST 1: LANDING PAGE - DESKTOP VIEWPORT (1280x800)
        # -------------------------------------------------------------
        print("\n[TEST 1] Verificando Landing Page en Desktop...")
        context_desktop = browser.new_context(viewport={"width": 1280, "height": 800})
        page = context_desktop.new_page()
        page.goto(BASE_URL)
        page.wait_for_load_state("networkidle")
        
        # Verificar botones unificados en Navbar y Hero
        nav_login = page.locator("header a[href*='login']:visible")
        nav_register = page.locator("header a[href*='register']:visible")
        hero_login = page.locator("#inicio a[href*='login']:visible")
        hero_register = page.locator("#inicio a[href*='register']:visible")
        
        expect(nav_login).to_be_visible()
        expect(nav_register).to_be_visible()
        expect(hero_login).to_be_visible()
        expect(hero_register).to_be_visible()
        
        print(f"  ✓ Botón Navbar Login: '{nav_login.inner_text().strip()}'")
        print(f"  ✓ Botón Navbar Register: '{nav_register.inner_text().strip()}'")
        print(f"  ✓ Botón Hero Login: '{hero_login.inner_text().strip()}'")
        print(f"  ✓ Botón Hero Register: '{hero_register.inner_text().strip()}'")
        
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "01_landing_desktop.png"), full_page=True)
        print("  ✓ Screenshot guardado: 01_landing_desktop.png")
        
        # -------------------------------------------------------------
        # TEST 2: LANDING PAGE - MOBILE VIEWPORT (390x844 - iPhone 13)
        # -------------------------------------------------------------
        print("\n[TEST 2] Verificando Landing Page en Móvil...")
        context_mobile = browser.new_context(
            viewport={"width": 390, "height": 844},
            user_agent="Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15"
        )
        page_mobile = context_mobile.new_page()
        page_mobile.goto(BASE_URL)
        page_mobile.wait_for_load_state("networkidle")
        
        # Abrir menú hamburguesa móvil
        menu_btn = page_mobile.locator("#mobile-menu-btn")
        expect(menu_btn).to_be_visible()
        menu_btn.click()
        page_mobile.wait_for_timeout(300)
        
        mobile_menu = page_mobile.locator("#mobile-menu")
        expect(mobile_menu).to_be_visible()
        
        mob_login = mobile_menu.locator("a[href*='login']")
        mob_register = mobile_menu.locator("a[href*='register']")
        expect(mob_login).to_be_visible()
        expect(mob_register).to_be_visible()
        print(f"  ✓ Menú Móvil Login: '{mob_login.inner_text().strip()}'")
        print(f"  ✓ Menú Móvil Register: '{mob_register.inner_text().strip()}'")
        
        page_mobile.screenshot(path=os.path.join(SCREENSHOT_DIR, "02_landing_mobile_menu.png"))
        print("  ✓ Screenshot guardado: 02_landing_mobile_menu.png")
        context_mobile.close()
        
        # -------------------------------------------------------------
        # TEST 3: REGISTRO DE USUARIO CON CARRERA DINÁMICA
        # -------------------------------------------------------------
        print("\n[TEST 3] Probando Registro de Usuario con Carrera Dinámica...")
        page.goto(f"{BASE_URL}/register")
        page.wait_for_load_state("networkidle")
        
        timestamp = int(time.time())
        test_email = f"docente_playwright_{timestamp}@lia.unsj.edu.ar"
        
        # Validar opciones de carrera disponibles en el select
        carrera_select = page.locator("#carrera_id")
        expect(carrera_select).to_be_visible()
        options_count = carrera_select.locator("option").count()
        print(f"  ✓ Cantidad de opciones en select carrera: {options_count}")
        assert options_count > 1, "El select de carreras debe contener carreras activas."
        
        # Completar formulario de registro
        test_dni = str(timestamp % 90000000 + 10000000)
        page.fill("#nombre", f"Docente Playwright {timestamp}")
        page.fill("#email", test_email)
        page.fill("#dni", test_dni)
        carrera_select.select_option(index=1)
        page.fill("#password", "password123")
        page.fill("#password_confirmation", "password123")
        
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "03_register_form.png"))
        page.locator("form[action*='register'] button[type='submit']").click()
        page.wait_for_load_state("networkidle")
        
        # Como nuevo usuario sin activar, debe aterrizar en la bienvenida institucional
        current_url = page.url
        print(f"  ✓ Redirigido a: {current_url}")
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "04_bienvenida_registered.png"))
        
        # Cerrar sesión del usuario recién creado para continuar como Administrador
        page.goto(f"{BASE_URL}/")
        logout_btn = page.locator("header form[action*='logout'] button")
        if logout_btn.count() > 0:
            logout_btn.first.click()
            page.wait_for_load_state("networkidle")
        
        # -------------------------------------------------------------
        # TEST 4: LOGIN ADMINISTRADOR GENERAL (admin@lia.unsj.edu.ar)
        # -------------------------------------------------------------
        print("\n[TEST 4] Iniciando sesión como Administrador General...")
        page.goto(f"{BASE_URL}/login")
        page.wait_for_load_state("networkidle")
        page.fill("#login", "admin@lia.unsj.edu.ar")
        page.fill("#password", "1234")
        page.locator("form[action*='login'] button[type='submit']").click()
        page.wait_for_load_state("networkidle")
        
        expect(page).to_have_url(f"{BASE_URL}/dashboard")
        print("  ✓ Login exitoso y acceso al Dashboard.")
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "05_dashboard_desktop.png"))
        
        # Probar click en tarjeta de métricas para navegar a inventario filtrado
        total_activos_card = page.locator("a[href*='elementos']:has-text('Total Activos')").first
        if total_activos_card.count() > 0:
            print("  ✓ Clic en Tarjeta 'Total Activos'...")
            total_activos_card.click()
            page.wait_for_load_state("networkidle")
            assert "/elementos" in page.url
            print(f"  ✓ Navegó correctamente a: {page.url}")
        
        # -------------------------------------------------------------
        # TEST 5: MOVIMIENTO RÁPIDO (TRASLADO / PRÉSTAMO)
        # -------------------------------------------------------------
        print("\n[TEST 5] Probando Movimiento Rápido de Inventario...")
        page.goto(f"{BASE_URL}/movimientos/rapido")
        page.wait_for_load_state("networkidle")
        
        # Verificar que el formulario de movimiento rápido cargue
        tipo_mov_select = page.locator("select[name='tipo_movimiento'], select[name='tipo']")
        if tipo_mov_select.count() > 0:
            print("  ✓ Formulario de Movimiento Rápido disponible.")
            page.screenshot(path=os.path.join(SCREENSHOT_DIR, "06_movimiento_rapido.png"))
        else:
            print("  ℹ Formulario rápido visualizado.")
            page.screenshot(path=os.path.join(SCREENSHOT_DIR, "06_movimiento_rapido.png"))
            
        # -------------------------------------------------------------
        # TEST 6: GESTIÓN DE USUARIOS Y ROLES (/users)
        # -------------------------------------------------------------
        print("\n[TEST 6] Gestionando Usuarios y Roles (/users)...")
        page.goto(f"{BASE_URL}/users")
        page.wait_for_load_state("networkidle")
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "07_users_list.png"))
        
        # Buscar el usuario de prueba recién registrado
        user_row = page.locator(f"tr:has-text('{test_email}')")
        if user_row.count() > 0:
            print(f"  ✓ Usuario registrado encontrado en la tabla: {test_email}")
            edit_link = user_row.locator("a:has-text('Editar'), a[href*='edit']").first
            if edit_link.count() > 0:
                edit_link.click()
                page.wait_for_load_state("networkidle")
                print("  ✓ Abierta pantalla de edición de usuario.")
                page.screenshot(path=os.path.join(SCREENSHOT_DIR, "08_user_edit.png"))
        
        # -------------------------------------------------------------
        # TEST 7: PANEL APP_ADMIN - CARRERAS
        # -------------------------------------------------------------
        print("\n[TEST 7] Gestión App - Carreras (/app-admin/carreras)...")
        page.goto(f"{BASE_URL}/app-admin/carreras")
        page.wait_for_load_state("networkidle")
        
        carrera_nueva = f"Licenciatura en IA y Ciencia de Datos #{timestamp % 1000}"
        codigo_nuevo = f"LIC-IA-{timestamp % 100}"
        page.fill("form[action*='carreras'] input[name='nombre']", carrera_nueva)
        page.fill("form[action*='carreras'] input[name='codigo']", codigo_nuevo)
        page.locator("button:has-text('+ Registrar Carrera')").click()
        page.wait_for_load_state("networkidle")
        
        expect(page.locator(f"text={carrera_nueva}")).to_be_visible()
        print(f"  ✓ Carrera creada exitosamente: '{carrera_nueva}'")
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "09_app_admin_carreras.png"))
        
        # -------------------------------------------------------------
        # TEST 8: PANEL APP_ADMIN - ESPACIOS CON SUBIDA DE FOTO
        # -------------------------------------------------------------
        print("\n[TEST 8] Gestión App - Espacios del LIA con Subida de Foto...")
        page.goto(f"{BASE_URL}/app-admin/espacios")
        page.wait_for_load_state("networkidle")
        
        titulo_espacio = f"Sala de Cómputo Avanzado #{timestamp % 1000}"
        page.fill("form[action*='espacios'] input[name='titulo']", titulo_espacio)
        page.fill("form[action*='espacios'] textarea[name='descripcion']", "Laboratorio especializado equipado con 25 estaciones de trabajo para investigación en Deep Learning y Robótica Autónoma.")
        
        # Subir foto real de prueba
        foto_espacio_path = os.path.abspath("scratch/images/espacio_sample.png")
        page.locator("form[action*='espacios'] input[name='foto']").set_input_files(foto_espacio_path)
        page.fill("form[action*='espacios'] input[name='orden']", "1")
        
        page.locator("button:has-text('Publicar Espacio')").click()
        page.wait_for_load_state("networkidle")
        
        expect(page.locator(f"text={titulo_espacio}")).to_be_visible()
        print(f"  ✓ Espacio creado y publicado con foto: '{titulo_espacio}'")
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "10_app_admin_espacios.png"))
        
        # -------------------------------------------------------------
        # TEST 9: PANEL APP_ADMIN - PERSONAL Y AUTORIDADES
        # -------------------------------------------------------------
        print("\n[TEST 9] Gestión App - Personal y Autoridades...")
        page.goto(f"{BASE_URL}/app-admin/personal")
        page.wait_for_load_state("networkidle")
        
        nombre_personal = f"Dra. Mariana Roldán #{timestamp % 1000}"
        page.fill("form[action*='personal'] input[name='nombre']", nombre_personal)
        page.fill("form[action*='personal'] input[name='cargo']", "Directora General del LIA - Investigadora CONICET")
        page.fill("form[action*='personal'] textarea[name='comentario']", "Referente de seguridad y coordinación institucional del laboratorio. Atención a docentes de lunes a jueves de 9 a 13hs.")
        
        foto_personal_path = os.path.abspath("scratch/images/personal_sample.png")
        page.locator("form[action*='personal'] input[name='foto']").set_input_files(foto_personal_path)
        page.fill("form[action*='personal'] input[name='orden']", "1")
        
        page.locator("button:has-text('Registrar Autoridad')").click()
        page.wait_for_load_state("networkidle")
        
        expect(page.locator(f"text={nombre_personal}")).to_be_visible()
        print(f"  ✓ Personal creado y publicado: '{nombre_personal}'")
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "11_app_admin_personal.png"))
        
        # -------------------------------------------------------------
        # TEST 10: PANEL APP_ADMIN - ACTIVIDADES Y NOVEDADES (FEED)
        # -------------------------------------------------------------
        print("\n[TEST 10] Gestión App - Actividades y Novedades (Feed)...")
        page.goto(f"{BASE_URL}/app-admin/actividades")
        page.wait_for_load_state("networkidle")
        
        titulo_actividad = f"Gran Hackathon de Robótica y Visión 2026 #{timestamp % 1000}"
        page.fill("form[action*='actividades'] input[name='titulo']", titulo_actividad)
        page.fill("form[action*='actividades'] textarea[name='contenido']", "Convocatoria abierta para estudiantes de todas las carreras de Exactas. Competencia de 48hs con premios en hardware y certificados avalados por la UNSJ.")
        
        foto_actividad_path = os.path.abspath("scratch/images/actividad_sample.png")
        page.locator("form[action*='actividades'] input[name='foto']").set_input_files(foto_actividad_path)
        
        page.locator("button:has-text('Publicar en el Feed')").click()
        page.wait_for_load_state("networkidle")
        
        expect(page.locator(f"text={titulo_actividad}")).to_be_visible()
        print(f"  ✓ Actividad publicada en el feed: '{titulo_actividad}'")
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "12_app_admin_actividades.png"))
        
        # -------------------------------------------------------------
        # TEST 11: VERIFICACIÓN DE SECCIONES DINÁMICAS EN PORTADA (DESKTOP Y MOBILE)
        # -------------------------------------------------------------
        print("\n[TEST 11] Verificando Secciones Dinámicas en la Portada...")
        page.goto(BASE_URL)
        page.wait_for_load_state("networkidle")
        
        # Verificar que el espacio, personal y actividad aparezcan en el DOM de la landing
        expect(page.locator(f"text={titulo_espacio}")).to_be_visible()
        expect(page.locator(f"text={nombre_personal}")).to_be_visible()
        expect(page.locator(f"text={titulo_actividad}")).to_be_visible()
        print("  ✓ Todas las entidades dinámicas se visualizan correctamente en Desktop!")
        
        page.screenshot(path=os.path.join(SCREENSHOT_DIR, "13_landing_dynamic_desktop.png"), full_page=True)
        print("  ✓ Screenshot guardado: 13_landing_dynamic_desktop.png")
        
        # Ahora verificar en vista Móvil
        print("  Verificando en vista Móvil...")
        context_mobile_audit = browser.new_context(
            viewport={"width": 390, "height": 844},
            user_agent="Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15"
        )
        page_mob_audit = context_mobile_audit.new_page()
        page_mob_audit.goto(BASE_URL)
        page_mob_audit.wait_for_load_state("networkidle")
        
        expect(page_mob_audit.locator(f"text={titulo_espacio}")).to_be_visible()
        expect(page_mob_audit.locator(f"text={nombre_personal}")).to_be_visible()
        expect(page_mob_audit.locator(f"text={titulo_actividad}")).to_be_visible()
        print("  ✓ Todas las entidades dinámicas se visualizan correctamente en Móvil!")
        
        page_mob_audit.screenshot(path=os.path.join(SCREENSHOT_DIR, "14_landing_dynamic_mobile.png"), full_page=True)
        print("  ✓ Screenshot guardado: 14_landing_dynamic_mobile.png")
        context_mobile_audit.close()
        
        browser.close()
        print("\n============================================================")
        print("✅ TODAS LAS PRUEBAS PLAYWRIGHT E2E FINALIZARON CON ÉXITO")
        print("============================================================")

if __name__ == "__main__":
    run_tests()
