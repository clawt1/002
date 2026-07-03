from playwright.sync_api import sync_playwright, expect
import os

def verify_ultimate(page):
    path = "file://" + os.path.abspath("index.html")
    page.goto(path)

    # 1. Login
    page.click("text=Administrateur")
    for digit in "0420":
        page.click(f"button.pin-btn:has-text('{digit}')")

    expect(page.locator("#home")).to_be_visible()

    # 2. Vente avec variante & remise
    page.click("nav button[data-screen='sell']")
    page.click("#sell .choice:has-text('Passant')")

    # Choisir Amnesia (Step 1)
    page.click("button.prod-card:has-text('Amnesia')")

    # Choisir Variante 5g (Step 2)
    page.click("button.cat-btn:has-text('5g')")
    page.click("#next") # Ajouter au panier

    # Vérifier récap (Step 3)
    expect(page.locator("text=Amnesia CBD (5g)")).to_be_visible()

    # Appliquer remise
    page.click("button:has-text('Remise')")
    page.fill("#dv", "50")
    page.click("#modal button:has-text('Appliquer')")

    # Valider & Encaisser
    page.click("#next")
    expect(page.locator("#receipt")).to_be_visible()
    page.click("#receipt button:has-text('Terminer')")

    # 3. Admin & Rapport Z
    page.click("nav button[data-screen='admin']")
    page.click("#zReport")
    expect(page.locator("#z-report")).to_be_visible()
    page.click("#z-report button:has-text('Fermer')")

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page(viewport={"width": 1024, "height": 768})
        try:
            verify_ultimate(page)
        finally:
            browser.close()
