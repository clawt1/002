from playwright.sync_api import sync_playwright, expect
import os

def verify_lunar(page):
    path = "file://" + os.path.abspath("index.html")
    page.goto(path)

    # 1. Login
    page.click("text=Administrateur")
    for digit in "0420":
        page.click(f"button.pin-btn:has-text('{digit}')")

    expect(page.locator("#home")).to_be_visible()

    # 2. Test Command Palette (Omnisearch)
    page.keyboard.press("Control+k")
    expect(page.locator("#omnisearch")).to_be_visible()
    page.fill("#omniInput", "Admin")
    expect(page.locator("#omniResults b:has-text('Admin')")).to_be_visible()
    page.keyboard.press("Escape")
    expect(page.locator("#omnisearch")).not_to_be_visible()

    # 3. Vérifier Traçabilité (Lots)
    page.click("nav button[data-screen='stock']")
    expect(page.locator("text=Lots: B001")).to_be_visible()

    # Vérifier IA Restock
    expect(page.locator("text=IA : Suggestions de réassort")).to_be_visible()

    # 4. Vérifier Heatmap
    page.click("nav button[data-screen='home']")
    expect(page.locator("#heatmap")).to_be_visible()

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": 1024, "height": 768})
        try:
            verify_lunar(page)
            print("LUNAR MISSION SUCCESSFUL")
        finally:
            browser.close()
