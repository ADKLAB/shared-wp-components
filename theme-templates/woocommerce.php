<?php get_header(); ?>

<?php if ( is_product() ) : ?>

    <!-- Одиночный товар -->
    <div class="shop-page">
        <?php woocommerce_content(); ?>
    </div>

<?php else : ?>

    <?php
    $current_cat  = get_queried_object();
    $current_slug = ( $current_cat && isset( $current_cat->slug ) ) ? $current_cat->slug : '';

    $categories = [
        'teploizolyatsiya-dlya-doma'       => 'Теплоизоляция для дома',
        'professionalnaya-teploizolyatsiya' => 'Профессиональная изоляция',
        'upakovochnye-materialy'            => 'Упаковочные материалы',
    ];
    ?>

    <div class="page-header">
        <div class="container">
            <h1>
                <?php
                if ( is_product_category() && isset( $categories[ $current_slug ] ) ) {
                    echo esc_html( $categories[ $current_slug ] );
                } else {
                    echo 'Каталог продукции';
                }
                ?>
            </h1>
            <p style="color:rgba(255,255,255,0.75);margin-top:8px;">
                Изоляционные и упаковочные материалы IZOterm и TRUBOFLEX. Оптовые поставки.
            </p>
        </div>
    </div>

    <div class="container catalog-layout">

        <!-- Фильтры -->
        <aside class="catalog-filters">
            <h3>Категории</h3>
            <ul class="filter-list">
                <li>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
                       class="<?php echo ! $current_slug ? 'active' : ''; ?>">
                        Все товары
                    </a>
                </li>
                <?php foreach ( $categories as $slug => $label ) :
                    $link = get_term_link( $slug, 'product_cat' );
                    if ( is_wp_error( $link ) ) continue;
                ?>
                <li>
                    <a href="<?php echo esc_url( $link ); ?>"
                       class="<?php echo $current_slug === $slug ? 'active' : ''; ?>">
                        <?php echo esc_html( $label ); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Товары -->
        <div class="catalog-products">
            <?php
            $paged    = max( 1, get_query_var( 'paged' ) );
            $tax_args = [];
            if ( $current_slug ) {
                $tax_args[] = [
                    'taxonomy' => 'product_cat',
                    'field'    => 'slug',
                    'terms'    => $current_slug,
                ];
            }

            $catalog_query = new WP_Query( [
                'post_type'      => 'product',
                'posts_per_page' => 12,
                'paged'          => $paged,
                'tax_query'      => $tax_args,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ] );
            ?>

            <?php if ( $catalog_query->have_posts() ) : ?>
                <ul class="products columns-3">
                    <?php while ( $catalog_query->have_posts() ) : $catalog_query->the_post();
                        global $product;
                        if ( ! $product instanceof WC_Product ) $product = wc_get_product( get_the_ID() );
                    ?>
                        <li class="product-card">
                            <a href="<?php the_permalink(); ?>" class="product-card__img-wrap">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'woocommerce_thumbnail', [ 'class' => 'product-card__img' ] ); ?>
                                <?php else : ?>
                                    <div class="product-card__img product-card__img--placeholder"></div>
                                <?php endif; ?>
                            </a>
                            <div class="product-card__body">
                                <h3 class="product-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <p class="product-card__price">
                                    <?php if ( $product ) echo $product->get_price_html(); ?>
                                </p>
                                <a href="<?php the_permalink(); ?>" class="btn-catalog">Подробнее</a>
                            </div>
                        </li>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ul>

                <div class="catalog-pagination">
                    <?php echo paginate_links( [
                        'total'     => $catalog_query->max_num_pages,
                        'current'   => $paged,
                        'prev_text' => '&larr;',
                        'next_text' => '&rarr;',
                    ] ); ?>
                </div>

            <?php else : ?>
                <p class="no-products">Товары не найдены.</p>
            <?php endif; ?>
        </div>

    </div>

<?php endif; ?>

<?php get_footer(); ?>
