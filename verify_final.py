import asyncio
from playwright.async_api import async_playwright
import os

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        page = await browser.new_page(viewport={'width': 1024, 'height': 768})

        # Load app
        await page.goto(f'file://{os.getcwd()}/index.html')
        await page.wait_for_selector('#lock')

        # Unlock admin
        await page.fill('#pin', '0420')
        await page.click('#unlock')
        await page.wait_for_selector('#workspace')
        await asyncio.sleep(0.5)

        # Dashboard
        await page.screenshot(path='final_01_dashboard.png')

        # Clients
        await page.click('button[data-screen="clients"]')
        await page.wait_for_selector('#rows')
        await page.screenshot(path='final_02_clients.png')

        # Stock
        await page.click('button[data-screen="stock"]')
        await page.wait_for_selector('#stock')
        await page.screenshot(path='final_03_stock.png')

        # Vente tactile
        await page.click('button[data-screen="sell"]')
        await page.wait_for_selector('button[data-client="c1"]')
        await page.click('button[data-client="c1"]') # Select client
        await page.click('#next') # Step 0 -> 1
        await page.wait_for_selector('button[data-product="p1"]')
        await page.click('button[data-product="p1"]') # Select product
        await page.click('#next') # Step 1 -> 2
        await page.wait_for_selector('button[data-qty="2"]')
        await page.click('button[data-qty="2"]') # Add qty
        await page.click('#next') # Step 2 -> 3 (Cart)
        await asyncio.sleep(0.5)
        await page.screenshot(path='final_04_cart.png')

        await browser.close()

if __name__ == "__main__":
    asyncio.run(run())
