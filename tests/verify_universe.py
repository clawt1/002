
import asyncio
from playwright.async_api import async_playwright
import os

async def verify_universe():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        path = os.path.abspath("index.html")
        await page.goto(f"file://{path}")
        await page.evaluate("localStorage.clear()")
        await page.reload()

        # 1. Unlock Admin
        await page.click("button.role:has-text('Administrateur')")
        for digit in "0420":
            await page.click(f"button.pin-btn:text-is('{digit}')")
        await page.wait_for_selector("#workspace:not(.hidden)")
        print("✅ Unlock success")

        # 2. Check Supply Chain
        await page.click("button[data-screen='supply']")
        await page.click("text=Nouvelle Commande")
        await page.select_option("#po_product", "p5") # Huile
        await page.fill("#po_qty", "10")
        await page.click("#savePO")
        await page.wait_for_selector("text=PO #")
        print("✅ Supply Chain PO created")

        # 3. Check Audit Log
        await page.click("button[data-screen='audit']")
        await page.wait_for_selector("text=COMMANDE_STOCK")
        print("✅ Audit Log tracing success")

        # 4. Check HR Performance
        await page.click("button[data-screen='hr']")
        await page.wait_for_selector("text=Performance Vendeurs")
        await page.wait_for_selector("text=Objectif Mensuel")
        print("✅ HR Module verified")

        # 5. Check Charts
        await page.click("button[data-screen='home']")
        await page.wait_for_selector("#catChart")
        print("✅ Advanced Analytics rendering")

        await page.screenshot(path="universe_verification.png", full_page=True)
        await browser.close()

if __name__ == "__main__":
    asyncio.run(verify_universe())
